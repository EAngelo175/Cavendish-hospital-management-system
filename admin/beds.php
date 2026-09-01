<?php
$page_title = "Beds & Admissions";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $id = (int) $_POST["id"];
    $status = $_POST["status"];
    $query = $conn->prepare("UPDATE beds SET status = ? WHERE id = ?");
    $query->execute([$status, $id]);

    audit($conn, "Updated bed #" . $id . " to " . $status, "beds");
    header("Location: beds.php");
    exit();
}

$beds = $conn
	->query("SELECT id, bed_number, ward AS ward_name, status FROM beds ORDER BY ward, bed_number")
    ->fetchAll();
$admissions = $conn
    ->query(
		"SELECT a.*, p.name patient_name, b.bed_number, b.ward AS ward_name
		 FROM admissions a
		 JOIN patients p ON p.id = a.patient_id
		 JOIN beds b ON b.id = a.bed_id
		 WHERE a.status = 'Admitted'
		 ORDER BY a.admission_date DESC",
    )
    ->fetchAll();
?>

<div class="page-actions">
	<div>
		<h2>Beds & admissions</h2>
		<p class="muted">Monitor capacity and admitted patients.</p>
	</div>
</div>

<div class="panel">
	<div class="table-wrap">
		<table>
			<tr>
				<th>Bed</th>
				<th>Ward</th>
				<th>Status</th>
				<th>Update</th>
			</tr>
			<?php foreach ($beds as $bed): ?>
				<tr>
					<td><?= e($bed["bed_number"]) ?></td>
					<td><?= e($bed["ward_name"]) ?></td>
					<td><?= e($bed["status"]) ?></td>
					<td>
						<form method="post" class="inline-form">
							<?= csrf_field() ?>
							<input type="hidden" name="id" value="<?= $bed["id"] ?>">
							<select name="status">
								<option <?= $bed["status"] === "Available"
            ? "selected"
            : "" ?>>Available</option>
								<option <?= $bed["status"] === "Occupied" ? "selected" : "" ?>>Occupied</option>
								<option <?= $bed["status"] === "Maintenance"
            ? "selected"
            : "" ?>>Maintenance</option>
							</select>
							<button class="mini-btn">Save</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>

<div class="panel">
	<h2>Current admissions</h2>
	<div class="table-wrap">
		<table>
			<tr>
				<th>Patient</th>
				<th>Ward</th>
				<th>Bed</th>
				<th>Date</th>
			</tr>
			<?php foreach ($admissions as $admission): ?>
				<tr>
					<td><?= e($admission["patient_name"]) ?></td>
					<td><?= e($admission["ward_name"]) ?></td>
					<td><?= e($admission["bed_number"]) ?></td>
					<td><?= e($admission["admission_date"]) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>

<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

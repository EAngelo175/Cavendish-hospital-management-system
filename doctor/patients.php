<?php
$page_title = "Doctor Patients";
require_once __DIR__ . "/../Includes/header.php";
require_role("doctor");
$query = trim($_GET["q"] ?? "");
$sql = "SELECT DISTINCT p.id, p.name, p.phone, p.email
        FROM patients p
        JOIN appointments a ON a.patient_id = p.id
        WHERE a.doctor_id = ?";
$params = [(int) $_SESSION["doctor_id"]];
if ($query !== "") {
    $sql .= " AND (p.name LIKE ? OR p.phone LIKE ? OR p.email LIKE ?)";
    $term = "%$query%";
    array_push($params, $term, $term, $term);
}
$sql .= " ORDER BY p.name";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>My patients</h2><p class="muted">Patients with appointments assigned to you.</p></div></div>
<form class="searchbar"><input name="q" value="<?= e($query) ?>" placeholder="Search patients"><button class="btn secondary">Search</button></form>
<div class="panel"><div class="table-wrap"><table><tr><th>Name</th><th>Phone</th><th>Email</th><th></th></tr>
<?php foreach ($patients as $patient): ?><tr><td><?= e($patient["name"]) ?></td><td><?= e($patient["phone"]) ?></td><td><?= e($patient["email"]) ?></td><td><a class="mini-btn" href="records.php?patient_id=<?= $patient["id"] ?>">Records</a></td></tr><?php endforeach; ?>
</table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

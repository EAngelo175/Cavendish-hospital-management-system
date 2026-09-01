<?php
$page_title = "Admissions";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");
$patients = $conn->query("SELECT id, name FROM patients ORDER BY name")->fetchAll();
$beds = $conn->query("SELECT id, bed_number, ward FROM beds WHERE status = 'Available' ORDER BY ward, bed_number")->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $patient_id = (int) $_POST["patient_id"];
    $bed_id = (int) $_POST["bed_id"];
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO admissions (patient_id, bed_id, admission_date, status) VALUES (?, ?, CURDATE(), 'Admitted')");
        $stmt->execute([$patient_id, $bed_id]);
        $conn->prepare("UPDATE beds SET status = 'Occupied' WHERE id = ? AND status = 'Available'")->execute([$bed_id]);
        $conn->commit();
        header("Location: admissions.php");
        exit();
    } catch (PDOException $exception) {
        if ($conn->inTransaction()) $conn->rollBack();
        $error = "Unable to admit patient.";
    }
}
$admissions = $conn->query("SELECT a.*, p.name patient_name, b.bed_number, b.ward FROM admissions a JOIN patients p ON p.id = a.patient_id JOIN beds b ON b.id = a.bed_id WHERE a.status = 'Admitted' ORDER BY a.admission_date DESC")->fetchAll();
?>
<div class="page-actions"><div><h2>Admissions</h2><p class="muted">Admit patients to available beds.</p></div></div><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): ?><option value="<?= $patient["id"] ?>"><?= e($patient["name"]) ?></option><?php endforeach; ?></select></label><label>Available bed<select name="bed_id" required><option value="">Choose bed</option><?php foreach ($beds as $bed): ?><option value="<?= $bed["id"] ?>"><?= e($bed["ward"] . " / " . $bed["bed_number"]) ?></option><?php endforeach; ?></select></label><button class="btn">Admit patient</button></form></div><div class="panel"><table><tr><th>Patient</th><th>Ward</th><th>Bed</th><th>Date</th></tr><?php foreach ($admissions as $admission): ?><tr><td><?= e($admission["patient_name"]) ?></td><td><?= e($admission["ward"]) ?></td><td><?= e($admission["bed_number"]) ?></td><td><?= e($admission["admission_date"]) ?></td></tr><?php endforeach; ?></table></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

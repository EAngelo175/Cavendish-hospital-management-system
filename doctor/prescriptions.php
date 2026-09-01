<?php
$page_title = "Prescription";
require_once __DIR__ . "/../Includes/header.php";
require_role("doctor");
$doctor_id = (int) $_SESSION["doctor_id"];
$patients_query = $conn->prepare("SELECT DISTINCT p.id, p.name FROM patients p JOIN appointments a ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.name");
$patients_query->execute([$doctor_id]);
$patients = $patients_query->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $patient_id = (int) ($_POST["patient_id"] ?? 0);
    $medicine = trim($_POST["medicine"] ?? "");
    $dosage = trim($_POST["dosage"] ?? "");
    $instructions = trim($_POST["instructions"] ?? "");
    $check = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND doctor_id = ?");
    $check->execute([$patient_id, $doctor_id]);
    if (!$check->fetchColumn() || $medicine === "" || $dosage === "") {
        $error = "Select your patient and provide medicine and dosage.";
    } else {
        $stmt = $conn->prepare("INSERT INTO prescriptions (doctor_id, patient_id, medicine, dosage, instructions, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$doctor_id, $patient_id, $medicine, $dosage, $instructions]);
        header("Location: prescriptions.php");
        exit();
    }
}
$stmt = $conn->prepare("SELECT r.*, p.name patient_name FROM prescriptions r JOIN patients p ON p.id = r.patient_id WHERE r.doctor_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$doctor_id]);
$prescriptions = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>Prescription</h2><p class="muted">Issue medicine instructions for your patients.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): ?><option value="<?= $patient["id"] ?>"><?= e($patient["name"]) ?></option><?php endforeach; ?></select></label><label>Medicine<input name="medicine" required></label><label>Dosage<input name="dosage" required></label><label>Prescriptions<textarea name="instructions"></textarea></label><button class="btn">Create prescription</button></form></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Medicine</th><th>Dosage</th><th>Status</th><th>Created</th></tr><?php foreach ($prescriptions as $prescription): ?><tr><td><?= e($prescription["patient_name"]) ?></td><td><?= e($prescription["medicine"]) ?></td><td><?= e($prescription["dosage"]) ?></td><td><?= e($prescription["status"]) ?></td><td><?= e($prescription["created_at"]) ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

<?php
$page_title = "Lab Requests";
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
    $test_name = trim($_POST["test_name"] ?? "");
    $check = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND doctor_id = ?");
    $check->execute([$patient_id, $doctor_id]);
    if (!$check->fetchColumn() || $test_name === "") {
        $error = "Select your patient and enter a test name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lab_tests (patient_id, test_name, status, doctor_id) VALUES (?, ?, 'Pending', ?)");
        $stmt->execute([$patient_id, $test_name, $doctor_id]);
        header("Location: lab_requests.php");
        exit();
    }
}
$stmt = $conn->prepare("SELECT l.*, p.name patient_name FROM lab_tests l JOIN patients p ON p.id = l.patient_id WHERE l.doctor_id = ? ORDER BY l.id DESC");
$stmt->execute([$doctor_id]);
$tests = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>Lab requests</h2><p class="muted">Request tests and review results.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): ?><option value="<?= $patient["id"] ?>"><?= e($patient["name"]) ?></option><?php endforeach; ?></select></label><label>Test name<input name="test_name" required></label><button class="btn">Request test</button></form></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Test</th><th>Status</th><th>Result</th></tr><?php foreach ($tests as $test): ?><tr><td><?= e($test["patient_name"]) ?></td><td><?= e($test["test_name"]) ?></td><td><?= e($test["status"]) ?></td><td><?= e($test["result"]) ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

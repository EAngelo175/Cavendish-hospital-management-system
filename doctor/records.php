<?php
$page_title = "Medical Records";
require_once __DIR__ . "/../Includes/header.php";
require_role("doctor");
$doctor_id = (int) $_SESSION["doctor_id"];
$patient_id = (int) ($_GET["patient_id"] ?? $_POST["patient_id"] ?? 0);
$patients_query = $conn->prepare("SELECT DISTINCT p.id, p.patient_code, p.name FROM patients p JOIN appointments a ON a.patient_id = p.id WHERE a.doctor_id = ? ORDER BY p.name");
$patients_query->execute([$doctor_id]);
$patients = $patients_query->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $diagnosis = trim($_POST["diagnosis"] ?? "");
    $notes = trim($_POST["notes"] ?? "");
    $treatment = "";
    if (!$patient_id || ($diagnosis === "" && $notes === "")) {
        $error = "Select a patient and enter the diagnosis or clinical notes.";
    } else {
        $check = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND doctor_id = ?");
        $check->execute([$patient_id, $doctor_id]);
        if (!$check->fetchColumn()) {
            $error = "That patient is not assigned to you.";
        } else {
            $doctor_name = $conn->prepare("SELECT name FROM doctors WHERE id = ? LIMIT 1");
            $doctor_name->execute([$doctor_id]);
            $doctor_name = $doctor_name->fetchColumn() ?: "Doctor";

            $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$patient_id, $doctor_id, $diagnosis, $treatment, $notes]);

            $patient_user = $conn->prepare("SELECT user_id FROM patients WHERE id = ? LIMIT 1");
            $patient_user->execute([$patient_id]);
            $patient_user_id = $patient_user->fetchColumn();
            if ($patient_user_id) {
                $notification = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
                $notification->execute([
                    (int) $patient_user_id,
                    "New medical record",
                    "Dr. " . $doctor_name . " added a new medical record for you.",
                ]);
            }

            header("Location: records.php?patient_id=$patient_id");
            exit();
        }
    }
}
$records = [];
if ($patient_id) {
    $stmt = $conn->prepare("SELECT r.*, p.name patient_name FROM medical_records r JOIN patients p ON p.id = r.patient_id WHERE r.patient_id = ? AND r.doctor_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$patient_id, $doctor_id]);
    $records = $stmt->fetchAll();
}
$diagnosis_options = [
    "Hypertension",
    "Diabetes mellitus",
    "Malaria",
    "Typhoid fever",
    "Upper respiratory infection",
    "Asthma",
    "Migraine",
    "Back pain",
    "Anemia",
    "Gastroenteritis",
    "Urinary tract infection",
    "Wound infection",
    "Dehydration",
    "Pregnancy monitoring",
    "General consultation",
];
?>
<div class="page-actions"><div><h2>Medical records</h2><p class="muted">Document diagnosis and clinical follow-up for each patient.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="grid two"><div class="panel" style="border:1px solid #c9d7e6;background:#f8fbff;box-shadow:0 2px 10px rgba(18,48,71,0.08);"><h2 style="color:#123047;">Add record</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): $patient_ref = $patient["patient_code"] ?: "PAT-" . str_pad((string) $patient["id"], 6, "0", STR_PAD_LEFT); ?><option value="<?= $patient["id"] ?>" <?= $patient_id === (int) $patient["id"] ? "selected" : "" ?>><?= e($patient_ref . " - " . $patient["name"]) ?></option><?php endforeach; ?></select></label><label>Diagnosis<input list="diagnosis-list" name="diagnosis" value="" placeholder="Select or type diagnosis" required><datalist id="diagnosis-list"><?php foreach ($diagnosis_options as $item): ?><option value="<?= e($item) ?>"><?php endforeach; ?></datalist></label><label>Clinical notes<textarea name="notes" placeholder="Add observations, findings, follow-up, and care plan."></textarea></label><button class="btn" style="background:#123047;">Save record</button></form></div><div class="panel" style="border:1px solid #c9d7e6;background:#f8fbff;box-shadow:0 2px 10px rgba(18,48,71,0.08);"><h2 style="color:#123047;">Saved records</h2><?php foreach ($records as $record): ?><article class="panel" style="margin-bottom:12px;border-left:4px solid #123047;background:#ffffff;"><strong><?= e($record["created_at"]) ?></strong><p><b>Diagnosis:</b> <?= e($record["diagnosis"]) ?></p><p><?= nl2br(e($record["notes"] ?: "No clinical notes recorded.")) ?></p></article><?php endforeach; ?></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

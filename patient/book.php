<?php $page_title = "Book Appointment";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$doctors = $conn
    ->query("SELECT DISTINCT specialization FROM doctors WHERE specialization IS NOT NULL AND specialization<>'' ORDER BY specialization")
    ->fetchAll();
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $specialization = trim($_POST["specialization"] ?? "");
    $date = $_POST["appointment_date"];
    $time = $_POST["appointment_time"];
    $reason = trim($_POST["reason"]);
    $doctor_record = assign_doctor_by_specialization($conn, $specialization);
    if (!$doctor_record) {
        $error = "Please choose a valid specialization with available doctors.";
    } else {
        $doctor = (int) $doctor_record["id"];
        if (!appointment_slot_available($conn, $doctor, $date, $time)) {
            $error = "This time is too close to another appointment for the same doctor. Please leave at least 20 minutes between visits.";
        } else {
            $s = $conn->prepare(
                "INSERT INTO appointments(patient_id,doctor_id,appointment_date,appointment_time,reason,status,created_at) VALUES(?,?,?,?,?,'Pending',NOW())",
            );
            $s->execute([$_SESSION["patient_id"], $doctor, $date, $time, $reason]);
            $success = "Appointment request submitted to Dr. " . $doctor_record["name"] . ". The doctor will review it.";
        }
    }
}
?><div class="page-actions"><div><h2>Book an appointment</h2><p class="muted">Choose the care specialization you need. We will assign an available doctor for you.</p></div></div><?php
if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif;
if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif;
?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Specialization<select name="specialization" required><option value="">Choose a specialization</option><?php foreach (
    $doctors
    as $d
): ?><option value="<?= e($d["specialization"]) ?>" <?= ($_POST["specialization"] ?? "") === $d["specialization"] ? "selected" : "" ?>><?= e($d["specialization"]) ?></option><?php endforeach; ?></select></label><label>Date<input type="date" name="appointment_date" min="<?= date(
    "Y-m-d",
) ?>" required></label><label>Time<input type="time" name="appointment_time" required></label><label>Reason<textarea name="reason" rows="4" required></textarea></label><div class="form-actions"><button class="btn">Request appointment</button></div></form></div><?php require_once __DIR__ .
    "/../Includes/footer.php"; ?>

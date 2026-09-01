<?php
$page_title = "Doctor Schedules";
require_once __DIR__ . "/../Includes/header.php";
require_role("admin");
$doctors = $conn->query("SELECT id, name FROM doctors ORDER BY name")->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $doctor_id = (int) $_POST["doctor_id"];
    $day = (int) $_POST["day_of_week"];
    $start = $_POST["start_time"];
    $end = $_POST["end_time"];
    if ($start >= $end) {
        $error = "End time must be after start time.";
    } else {
        $stmt = $conn->prepare("INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, status) VALUES (?, ?, ?, ?, 'Available')");
        $stmt->execute([$doctor_id, $day, $start, $end]);
        header("Location: doctor_schedule.php");
        exit();
    }
}
$schedules = $conn->query("SELECT s.*, d.name doctor_name FROM doctor_schedule s JOIN doctors d ON d.id = s.doctor_id ORDER BY d.name, s.day_of_week, s.start_time")->fetchAll();
$days = [1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 4 => "Thursday", 5 => "Friday", 6 => "Saturday", 7 => "Sunday"];
?>
<div class="page-actions"><div><h2>Doctor schedules</h2><p class="muted">Set available consultation hours.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Doctor<select name="doctor_id" required><option value="">Choose doctor</option><?php foreach ($doctors as $doctor): ?><option value="<?= $doctor["id"] ?>"><?= e($doctor["name"]) ?></option><?php endforeach; ?></select></label><label>Day<select name="day_of_week"><?php foreach ($days as $number => $name): ?><option value="<?= $number ?>"><?= $name ?></option><?php endforeach; ?></select></label><label>Start<input type="time" name="start_time" required></label><label>End<input type="time" name="end_time" required></label><button class="btn">Add schedule</button></form></div><div class="panel"><div class="table-wrap"><table><tr><th>Doctor</th><th>Day</th><th>Hours</th><th>Status</th></tr><?php foreach ($schedules as $schedule): ?><tr><td><?= e($schedule["doctor_name"]) ?></td><td><?= e($days[$schedule["day_of_week"]] ?? "") ?></td><td><?= e($schedule["start_time"]) ?> - <?= e($schedule["end_time"]) ?></td><td><?= e($schedule["status"]) ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

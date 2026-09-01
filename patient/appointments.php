<?php $page_title = "My Appointments";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$s = $conn->prepare(
    "SELECT a.*,d.name doctor_name,d.specialization FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.patient_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC",
);
$s->execute([$_SESSION["patient_id"]]);
$rows = $s->fetchAll();
?><div class="page-actions"><div><h2>My appointments</h2></div><a class="btn" href="book.php">+ Book appointment</a></div><div class="panel"><div class="table-wrap"><table><tr><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th></tr><?php foreach (
    $rows
    as $r
): ?><tr><td><?= e($r["doctor_name"]) ?></td><td><?= e(
    $r["specialization"],
) ?></td><td><?= e($r["appointment_date"]) ?></td><td><?= e(
    $r["appointment_time"],
) ?></td><td><?= e($r["status"]) ?></td><td><?= e(
    $r["reason"],
) ?></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php"; ?>

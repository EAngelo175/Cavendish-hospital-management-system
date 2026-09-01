<?php $page_title = "Doctor Appointments";
require_once __DIR__ . "/../Includes/header.php";
require_role("doctor");
$s = $conn->prepare(
    "SELECT a.*,p.name patient_name,p.phone FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE a.doctor_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC",
);
$s->execute([$_SESSION["doctor_id"]]);
$rows = $s->fetchAll();
?><div class="page-actions"><div><h2>My appointments</h2><p class="muted">Home bookings and hospital appointments.</p></div></div><div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Phone</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr><?php foreach (
    $rows
    as $r
): ?><tr><td><?= e($r["patient_name"]) ?></td><td><?= e(
    $r["phone"],
) ?></td><td><?= e($r["appointment_date"]) ?></td><td><?= e(
    $r["appointment_time"],
) ?></td><td><?= e(
    $r["status"],
) ?></td><td><a class="mini-btn" href="appointment_view.php?id=<?= $r[
    "id"
] ?>">Manage</a></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php"; ?>

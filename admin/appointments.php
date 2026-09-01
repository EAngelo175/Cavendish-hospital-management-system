<?php $page_title = "Appointment Management";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$st = $_GET["status"] ?? "";
$sql =
    "SELECT a.*,p.name patient_name,d.name doctor_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id WHERE 1=1";
$p = [];
if (
    in_array(
        $st,
        ["Pending", "Approved", "Completed", "Cancelled", "Rescheduled"],
        true,
    )
) {
    $sql .= " AND a.status=?";
    $p[] = $st;
}
$sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$q = $conn->prepare($sql);
$q->execute($p);
$rows = $q->fetchAll();
?><div class="page-actions"><div><h2>Appointments</h2><p class="muted">Review patient bookings made from home and staff-created appointments.</p></div></div><form class="searchbar"><select name="status"><option value="">All statuses</option><?php foreach (
    ["Pending", "Approved", "Completed", "Cancelled", "Rescheduled"]
    as $x
): ?><option <?= $st === $x
    ? "selected"
    : "" ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn secondary">Filter</button></form><div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th><th></th></tr><?php foreach (
    $rows
    as $r
): ?><tr><td><?= e($r["patient_name"]) ?></td><td><?= e(
    $r["doctor_name"],
) ?></td><td><?= e($r["appointment_date"]) ?></td><td><?= e(
    $r["appointment_time"],
) ?></td><td><?= e($r["status"]) ?></td><td><?= e(
    $r["reason"],
) ?></td><td><a class="mini-btn" href="appointment_view.php?id=<?= $r[
    "id"
] ?>">Manage</a></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

<?php $page_title = "Doctors";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$rows = $conn
    ->query(
        "SELECT d.*,COUNT(a.id) appointments FROM doctors d LEFT JOIN appointments a ON a.doctor_id=d.id GROUP BY d.id ORDER BY d.name",
    )
    ->fetchAll();
?><div class="page-actions"><div><h2>Doctors</h2><p class="muted">Manage doctors and appointment schedules.</p></div><a class="btn" href="doctor_create.php">+ Add doctor</a></div><div class="panel"><div class="table-wrap"><table><tr><th>Name</th><th>Specialization</th><th>Phone</th><th>Email</th><th>Appointments</th><th></th></tr><?php foreach (
    $rows
    as $d
): ?><tr><td><?= e($d["name"]) ?></td><td><?= e(
    $d["specialization"],
) ?></td><td><?= e($d["phone"]) ?></td><td><?= e($d["email"]) ?></td><td><?= $d[
    "appointments"
] ?></td><td><a class="mini-btn" href="doctor_edit.php?id=<?= $d[
    "id"
] ?>">Edit</a> <a class="mini-btn" href="doctor_schedule.php?id=<?= $d[
    "id"
] ?>">Schedule</a></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

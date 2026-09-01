<?php $page_title = "Patient Profile";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$id = (int) $_GET["id"];
$q = $conn->prepare(
    "SELECT p.*, u.created_at registered_at
     FROM patients p
     LEFT JOIN users u ON u.id = p.user_id
     WHERE p.id = ?",
);
$q->execute([$id]);
$p = $q->fetch();
if (!$p) {
    exit("Patient not found.");
}
$q = $conn->prepare(
    "SELECT a.*,d.name doctor_name FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.patient_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 10",
);
$q->execute([$id]);
$aps = $q->fetchAll();
$patient_code = $p["patient_code"] ?: "PAT-" . str_pad((string) $p["id"], 6, "0", STR_PAD_LEFT);
?><div class="page-actions"><div><h2><?= e(
    $p["name"],
) ?></h2><p class="muted">Patient ID <?= e($patient_code) ?></p></div><a class="btn secondary" href="patients.php">Back</a></div><div class="grid three"><div class="panel"><h2>Contact</h2><p><?= e(
    $p["phone"],
) ?></p><p><?= e($p["email"]) ?></p><p><?= e(
    $p["address"],
) ?></p></div><div class="panel"><h2>Profile</h2><p>Gender: <?= e(
    $p["gender"],
) ?></p><p>Age: <?= e($p["age"]) ?></p><p>DOB: <?= e($p["dob"]) ?></p><p>Blood group: <?= e(
    $p["blood_group"],
) ?></p></div><div class="panel"><h2>Portal</h2><p>Patient code: <?= e($patient_code) ?></p><p>Registered: <?= e(
    $p["registered_at"],
) ?></p></div></div><div class="panel"><h2>Recent appointments</h2><div class="table-wrap"><table><tr><th>Date</th><th>Doctor</th><th>Status</th><th>Reason</th></tr><?php foreach (
    $aps
    as $a
): ?><tr><td><?= e(
    $a["appointment_date"] . " " . $a["appointment_time"],
) ?></td><td><?= e($a["doctor_name"]) ?></td><td><?= e(
    $a["status"],
) ?></td><td><?= e(
    $a["reason"],
) ?></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

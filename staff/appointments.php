<?php
$page_title = "Appointment Desk";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");
$appointments = $conn
    ->query("SELECT a.*, p.name patient, d.name doctor FROM appointments a LEFT JOIN patients p ON p.id = a.patient_id LEFT JOIN doctors d ON d.id = a.doctor_id ORDER BY a.appointment_date ASC, a.appointment_time ASC")
    ->fetchAll();
?>
<div class="page-actions"><div><h2>Appointment desk</h2><p class="muted">Review the hospital appointment schedule.</p></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr><?php foreach ($appointments as $appointment): ?><tr><td><?= e($appointment["patient"]) ?></td><td><?= e($appointment["doctor"]) ?></td><td><?= e($appointment["appointment_date"]) ?></td><td><?= e($appointment["appointment_time"]) ?></td><td><?= e($appointment["status"]) ?></td><td><a class="mini-btn" href="appointment_view.php?id=<?= $appointment["id"] ?>">Review</a></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

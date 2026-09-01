<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role("patient");
$s = $conn->prepare(
    "SELECT a.*,d.name doctor_name,d.specialization FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.patient_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC",
);
$s->execute([$u["patient_id"]]);
json_response(["success" => true, "appointments" => $s->fetchAll()]); ?>

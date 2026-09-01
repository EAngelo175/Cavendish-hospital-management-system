<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role("doctor");
$s = $conn->prepare(
    "SELECT a.*,p.name patient_name,p.phone patient_phone FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE a.doctor_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC",
);
$s->execute([$u["doctor_id"]]);
json_response(["success" => true, "appointments" => $s->fetchAll()]); ?>

<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role("patient");
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "POST required"], 405);
}
$d = api_input();
$specialization = trim($d["specialization"] ?? "");
$date = $d["appointment_date"] ?? "";
$time = $d["appointment_time"] ?? "";
$reason = trim($d["reason"] ?? "");
$doctor_query = $conn->prepare(
        "SELECT d.id,d.name
         FROM doctors d
         LEFT JOIN appointments a
             ON a.doctor_id=d.id AND a.status IN ('Pending','Approved')
         WHERE d.specialization=?
         GROUP BY d.id,d.name
         ORDER BY COUNT(a.id),d.name
         LIMIT 1",
);
$doctor_query->execute([$specialization]);
$doctor_record = $doctor_query->fetch() ?: null;
if (
    !$u["patient_id"] ||
    !$doctor_record ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
    !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time) ||
    $reason === ""
) {
    json_response(
        ["success" => false, "message" => "Missing or invalid booking data"],
        422,
    );
}
$doctor = (int) $doctor_record["id"];
if (!appointment_slot_available($conn, $doctor, $date, $time)) {
    json_response(
        ["success" => false, "message" => "Selected time is too close to another appointment. Please leave at least 20 minutes between bookings."],
        409,
    );
}
$s = $conn->prepare(
    "INSERT INTO appointments(patient_id,doctor_id,appointment_date,appointment_time,reason,status,created_at) VALUES(?,?,?,?,?,'Pending',NOW())",
);
$s->execute([$u["patient_id"], $doctor, $date, $time, $reason]);
$id = (int) $conn->lastInsertId();
$ds = $conn->prepare("SELECT user_id FROM users WHERE doctor_id=?");
$ds->execute([$doctor]);
if ($du = $ds->fetchColumn()) {
    $n = $conn->prepare(
        "INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)",
    );
    $n->execute([
        $du,
        "New appointment request",
        "A patient has requested an appointment on " .
        $date .
        " at " .
        $time .
        ".",
    ]);
}
json_response(
    [
        "success" => true,
        "message" => "Appointment request submitted",
        "appointment_id" => $id,
    ],
    201,
); ?>

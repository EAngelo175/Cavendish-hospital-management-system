<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role("doctor");
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "POST required"], 405);
}
$d = api_input();
$id = (int) ($d["appointment_id"] ?? 0);
$status = $d["status"] ?? "";
if (
    !in_array(
        $status,
        ["Approved", "Completed", "Cancelled", "Rescheduled"],
        true,
    )
) {
    json_response(["success" => false, "message" => "Invalid status"], 422);
}
$s = $conn->prepare("SELECT * FROM appointments WHERE id=? AND doctor_id=?");
$s->execute([$id, $u["doctor_id"]]);
$a = $s->fetch();
if (!$a) {
    json_response(
        ["success" => false, "message" => "Appointment not found"],
        404,
    );
}
$conn
    ->prepare("UPDATE appointments SET status=? WHERE id=?")
    ->execute([$status, $id]);
$p = $conn->prepare("SELECT user_id, name, email, patient_code FROM patients WHERE id=?");
$p->execute([$a["patient_id"]]);
if ($patient = $p->fetch()) {
    $uid = (int) ($patient["user_id"] ?? 0);
    $n = $conn->prepare(
        "INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)",
    );
    $n->execute([
        $uid,
        "Appointment update",
        "Your appointment on " .
        $a["appointment_date"] .
        " at " .
        $a["appointment_time"] .
        " is now " .
        $status .
        ".",
    ]);
    if ($status === "Approved") {
        $email_to = "thtephane111@gmail.com";
        $n->execute([
            $uid,
            "Verification required",
            "Your appointment has been approved. Please verify your details before the consultation.",
        ]);
        $doctor_name = $conn->prepare("SELECT name FROM doctors WHERE id = ? LIMIT 1");
        $doctor_name->execute([$a["doctor_id"]]);
        $doctor_name = (string) ($doctor_name->fetchColumn() ?: "Doctor");
        send_patient_verification_email(
            $email_to,
            "Appointment Approval Verification - " . HOSPITAL_NAME,
            build_patient_verification_email(
                (string) ($patient["name"] ?? "Patient"),
                (string) ($patient["patient_code"] ?? "N/A"),
                $doctor_name,
                (string) $a["appointment_date"],
                (string) $a["appointment_time"]
            )
        );
    }
}
json_response(["success" => true, "message" => "Appointment updated"]); ?>

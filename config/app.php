<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax",
    ]);
    session_start();
}

// Session timeout configuration (30 minutes in seconds)
define("SESSION_TIMEOUT", (int) getenv("SESSION_TIMEOUT") ?: 1800);

// Check for session timeout
if (isset($_SESSION["user_id"])) {
    $current_time = time();
    $last_activity = $_SESSION["last_activity"] ?? $current_time;
    
    if ($current_time - $last_activity > SESSION_TIMEOUT) {
        // Session expired, destroy it
        session_destroy();
        header("Location: " . (getenv("APP_URL") ?: "/Hospital_Management_System") . "/login.php?timeout=1");
        exit();
    }
    
    // Update last activity time
    $_SESSION["last_activity"] = $current_time;
}

define(
    "BASE_URL",
    rtrim(getenv("APP_URL") ?: "/Hospital_Management_System", "/"),
);
define("HOSPITAL_NAME", getenv("HOSPITAL_NAME") ?: "CAVENDISH INTERNATIONAL HOSPITAL");
define("HOSPITAL_LOGO", BASE_URL . "/images/hospital logo.jpeg 1.jpeg");
date_default_timezone_set(getenv("APP_TIMEZONE") ?: "Africa/Kampala");
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/../security/auth.php";
require_once __DIR__ . "/../security/csrf.php";
require_once __DIR__ . "/../security/permissions.php";
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
function assign_doctor_by_specialization(PDO $conn, string $specialization): ?array
{
    $stmt = $conn->prepare(
        "SELECT d.id,d.name
         FROM doctors d
         LEFT JOIN appointments a
           ON a.doctor_id=d.id AND a.status IN ('Pending','Approved')
         WHERE d.specialization=?
         GROUP BY d.id,d.name
         ORDER BY COUNT(a.id),d.name
         LIMIT 1",
    );
    $stmt->execute([$specialization]);
    $doctor = $stmt->fetch();
    return $doctor ?: null;
}
function appointment_slot_available(PDO $conn, int $doctorId, string $date, string $time): bool
{
    $newSlot = strtotime($date . " " . $time);
    if ($newSlot === false) {
        return false;
    }

    $stmt = $conn->prepare(
        "SELECT appointment_time FROM appointments
         WHERE doctor_id = ? AND appointment_date = ? AND status IN ('Pending','Approved')
         ORDER BY appointment_time ASC",
    );
    $stmt->execute([$doctorId, $date]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingSlot = strtotime($date . " " . $row["appointment_time"]);
        if ($existingSlot === false) {
            continue;
        }

        $diffMinutes = abs((int) (($newSlot - $existingSlot) / 60));
        if ($diffMinutes < 20) {
            return false;
        }
    }

    return true;
}
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit();
}
function audit(PDO $db, string $action, string $module): void
{
    $stmt = $db->prepare(
        "INSERT INTO audit_logs(user_id,username,action,module,ip_address,created_at) VALUES(?,?,?,?,?,NOW())",
    );
    $stmt->execute([
        $_SESSION["user_id"] ?? null,
        $_SESSION["username"] ?? "system",
        $action,
        $module,
        $_SERVER["REMOTE_ADDR"] ?? null,
    ]);
}
function send_patient_verification_email(string $to_email, string $subject, string $message): bool
{
    $to = trim($to_email) ?: "thtephane111@gmail.com";
    $headers = [
        "From: " . (getenv("MAIL_FROM") ?: "no-reply@hospital.local"),
        "Reply-To: " . (getenv("MAIL_FROM") ?: "no-reply@hospital.local"),
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
    ];

    if (function_exists("mail") && @mail($to, $subject, $message, implode("\r\n", $headers))) {
        return true;
    }

    return false;
}
function build_patient_verification_email(string $patient_name, string $patient_code, string $doctor_name, string $appointment_date, string $appointment_time): string
{
    $name = trim($patient_name) !== "" ? $patient_name : "Patient";
    $code = trim($patient_code) !== "" ? $patient_code : "N/A";
    $doctor = trim($doctor_name) !== "" ? $doctor_name : "Doctor";

    return "Dear " . $name . ",\n\n"
        . "We are pleased to inform you that your consultation appointment has been approved by Dr. " . $doctor . ".\n\n"
        . "Please verify the details below before attending your consultation at " . HOSPITAL_NAME . ".\n\n"
        . "Patient Name: " . $name . "\n"
        . "Patient ID: " . $code . "\n"
        . "Doctor: Dr. " . $doctor . "\n"
        . "Appointment Date: " . $appointment_date . "\n"
        . "Appointment Time: " . $appointment_time . "\n\n"
        . "Kindly confirm that the information is correct and ensure you arrive on time for your appointment. If there are any changes or concerns, please contact the hospital desk immediately.\n\n"
        . "Thank you for choosing " . HOSPITAL_NAME . ". We look forward to serving you.";
}
function send_payment_confirmation_email(string $patient_email, string $subject, string $message): bool
{
    $to = trim($patient_email);
    if ($to === "") {
        return false;
    }
    
    $headers = [
        "From: " . (getenv("MAIL_FROM") ?: "no-reply@hospital.local"),
        "Reply-To: " . (getenv("MAIL_FROM") ?: "no-reply@hospital.local"),
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
    ];

    if (function_exists("mail") && @mail($to, $subject, $message, implode("\r\n", $headers))) {
        return true;
    }

    return false;
}
function build_payment_confirmation_email(string $patient_name, string $patient_code, string $invoice_number, float $amount, string $payment_method, string $balance_remaining): string
{
    $name = trim($patient_name) !== "" ? $patient_name : "Patient";
    $code = trim($patient_code) !== "" ? $patient_code : "N/A";
    
    return "Dear " . $name . ",\n\n"
        . "We have received your payment for your medical services at " . HOSPITAL_NAME . ".\n\n"
        . "Payment Details:\n"
        . "================\n"
        . "Patient Name: " . $name . "\n"
        . "Patient ID: " . $code . "\n"
        . "Invoice Number: " . $invoice_number . "\n"
        . "Amount Paid: UGX " . number_format($amount) . "\n"
        . "Payment Method: " . $payment_method . "\n"
        . "Date: " . date("Y-m-d H:i:s") . "\n\n"
        . ($balance_remaining > 0 ? "Outstanding Balance: UGX " . number_format((float) $balance_remaining) . "\n" : "Status: FULLY PAID ✓\n") . "\n"
        . "Thank you for choosing " . HOSPITAL_NAME . ". If you have any questions regarding this receipt, please contact our billing department.\n\n"
        . "Best regards,\n"
        . HOSPITAL_NAME . " Accounting Department";
}

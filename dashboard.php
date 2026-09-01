<?php
require_once __DIR__ . "/config/app.php";
if (empty($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
$role = $_SESSION["role"] ?? "";
$targets = [
    "admin" => "admin/dashboard.php",
    "doctor" => "doctor/appointments.php",
    "patient" => "patient/dashboard.php",
    "receptionist" => "staff/dashboard.php",
    "pharmacist" => "staff/dashboard.php",
    "lab" => "staff/dashboard.php",
    "accountant" => "staff/dashboard.php",
];
header("Location: " . BASE_URL . "/" . ($targets[$role] ?? "login.php"));
exit();

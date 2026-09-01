<?php
require_once __DIR__ . "/../config/app.php";
require_role("admin");

$from = $_GET["from"] ?? date("Y-m-01");
$to = $_GET["to"] ?? date("Y-m-d");
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=admin-report-" . $from . "-to-" . $to . ".csv");
$output = fopen("php://output", "w");
fputcsv($output, [HOSPITAL_NAME . " Administration Report"]);
fputcsv($output, ["From", $from, "To", $to]);
fputcsv($output, []);
fputcsv($output, ["Metric", "Value"]);
$query = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN ? AND ?");
$query->execute([$from, $to]);
fputcsv($output, ["Appointments", $query->fetchColumn()]);
$query = $conn->prepare("SELECT COUNT(*) FROM patients p LEFT JOIN users u ON u.id = p.user_id WHERE DATE(u.created_at) BETWEEN ? AND ?");
$query->execute([$from, $to]);
fputcsv($output, ["New patients", $query->fetchColumn()]);
$query = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM billing_payments WHERE DATE(payment_date) BETWEEN ? AND ?");
$query->execute([$from, $to]);
fputcsv($output, ["Collections", $query->fetchColumn()]);
fputcsv($output, []);
fputcsv($output, ["Appointment status", "Count"]);
$rows = $conn->query("SELECT status, COUNT(*) total FROM appointments GROUP BY status ORDER BY total DESC")->fetchAll();
foreach ($rows as $row) fputcsv($output, [$row["status"], $row["total"]]);
fclose($output);
exit();

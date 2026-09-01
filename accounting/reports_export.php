<?php
require_once __DIR__ . "/../config/app.php";
require_role("accountant");
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=finance-report-" . date("Y-m-d") . ".csv");
$output = fopen("php://output", "w");
fputcsv($output, [HOSPITAL_NAME . " Finance Report"]);
fputcsv($output, []);
fputcsv($output, ["Metric", "Value"]);
fputcsv($output, ["Invoices", $conn->query("SELECT COUNT(*) FROM invoices")->fetchColumn()]);
fputcsv($output, ["Paid invoices", $conn->query("SELECT COUNT(*) FROM invoices WHERE status = 'Paid'")->fetchColumn()]);
fputcsv($output, ["Total billed", $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM invoices")->fetchColumn()]);
fputcsv($output, ["Total collected", $conn->query("SELECT COALESCE(SUM(amount), 0) FROM billing_payments")->fetchColumn()]);
fputcsv($output, []);
fputcsv($output, ["Payment method", "Total collected"]);
$rows = $conn->query("SELECT payment_method, SUM(amount) total FROM billing_payments GROUP BY payment_method ORDER BY total DESC")->fetchAll();
foreach ($rows as $row) fputcsv($output, [$row["payment_method"], $row["total"]]);
fclose($output);
exit();

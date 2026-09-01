<?php
$page_title = "Finance Reports";
require_once __DIR__ . "/../Includes/header.php";
require_role("accountant");
$summary = [
    "Invoices" => $conn->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
    "Paid invoices" => $conn->query("SELECT COUNT(*) FROM invoices WHERE status = 'Paid'")->fetchColumn(),
    "Total billed" => $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM invoices")->fetchColumn(),
    "Total collected" => $conn->query("SELECT COALESCE(SUM(amount), 0) FROM billing_payments")->fetchColumn(),
];
$method_rows = $conn->query("SELECT payment_method, SUM(amount) total FROM billing_payments GROUP BY payment_method ORDER BY total DESC")->fetchAll();
$max_method = max(1, ...(array_column($method_rows, "total") ?: [0]));
?>
<div class="page-actions"><div><h2>Finance reports</h2><p class="muted">Summary of billing and collections.</p></div><div class="actions no-print"><button class="btn" type="button" onclick="window.print()">Print report</button><a class="btn secondary" href="reports_export.php">Export to Excel</a></div></div>
<div class="cards"><?php foreach ($summary as $label => $value): ?><div class="card"><span><?= e($label) ?></span><strong><?= str_contains($label, "billed") || str_contains($label, "collected") ? "UGX " . number_format((float) $value) : e((string) $value) ?></strong></div><?php endforeach; ?></div>
<div class="panel report-chart"><h2>Collections by payment method</h2><?php foreach ($method_rows as $method_row): ?><div class="chart-row"><span><?= e($method_row["payment_method"]) ?></span><div class="chart-track"><div class="chart-bar" style="width: <?= ((float) $method_row["total"] / $max_method) * 100 ?>%"></div></div><strong>UGX <?= number_format((float) $method_row["total"]) ?></strong></div><?php endforeach; ?></div>
<div class="panel report-graph"><h2>Payment graph</h2><div class="graph-bars"><?php foreach ($method_rows as $method_row): ?><div class="graph-column"><strong>UGX <?= number_format((float) $method_row["total"]) ?></strong><div class="graph-column-bar" style="height: <?= ((float) $method_row["total"] / $max_method) * 100 ?>%"></div><span><?= e($method_row["payment_method"]) ?></span></div><?php endforeach; ?></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

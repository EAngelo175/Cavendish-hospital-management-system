<?php
$page_title = "Administration Reports";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);

$from = $_GET["from"] ?? date("Y-m-01");
$to = $_GET["to"] ?? date("Y-m-d");

$query = $conn->prepare(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN ? AND ?",
);
$query->execute([$from, $to]);
$appointments = (int) $query->fetchColumn();

$query = $conn->prepare(
    "SELECT COUNT(*)
     FROM patients p
     LEFT JOIN users u ON u.id = p.user_id
     WHERE u.created_at BETWEEN ? AND ?",
);
$query->execute([$from, $to]);
$patients = (int) $query->fetchColumn();

$query = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0)
     FROM billing_payments
     WHERE DATE(payment_date) BETWEEN ? AND ?",
);
$query->execute([$from, $to]);
$revenue = (float) $query->fetchColumn();

$status_rows = $conn
    ->query("SELECT status, COUNT(*) total FROM appointments GROUP BY status ORDER BY total DESC")
    ->fetchAll();
$max_status = max(1, ...(array_column($status_rows, "total") ?: [0]));
?>
<div class="page-actions">
    <div>
        <h2>Administration reports</h2>
        <p class="muted">Review hospital activity and collections.</p>
    </div>
</div>

<div class="panel no-print">
    <form class="searchbar">
        <label>From<input type="date" name="from" value="<?= e($from) ?>"></label>
        <label>To<input type="date" name="to" value="<?= e($to) ?>"></label>
        <button class="btn">Run report</button>
        <button class="btn secondary" type="button" onclick="window.print()">Print report</button>
        <a class="btn secondary" href="reports_export.php?from=<?= e($from) ?>&amp;to=<?= e($to) ?>">Export to Excel</a>
    </form>
</div>

<div class="cards">
    <div class="card"><h3>Appointments</h3><strong><?= $appointments ?></strong></div>
    <div class="card"><h3>New patients</h3><strong><?= $patients ?></strong></div>
    <div class="card"><h3>Collections</h3><strong>UGX <?= number_format($revenue) ?></strong></div>
</div>

<div class="panel report-chart">
    <h2>Appointment status</h2>
    <?php foreach ($status_rows as $status_row): ?>
        <div class="chart-row">
            <span><?= e($status_row["status"]) ?></span>
            <div class="chart-track"><div class="chart-bar" style="width: <?= ((int) $status_row["total"] / $max_status) * 100 ?>%"></div></div>
            <strong><?= e($status_row["total"]) ?></strong>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel report-graph">
    <h2>Status graph</h2>
    <div class="graph-bars"><?php foreach ($status_rows as $status_row): ?><div class="graph-column"><strong><?= e($status_row["total"]) ?></strong><div class="graph-column-bar" style="height: <?= ((int) $status_row["total"] / $max_status) * 100 ?>%"></div><span><?= e($status_row["status"]) ?></span></div><?php endforeach; ?></div>
</div>

<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

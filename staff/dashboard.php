<?php
$page_title = "Staff Dashboard";
require_once __DIR__ . "/../Includes/header.php";
require_role(["receptionist", "pharmacist", "lab", "accountant"]);

$role = $_SESSION["role"];
$stats = [];
$rows = [];
$columns = [];

if ($role === "receptionist") {
    $stats = [
        "Today's appointments" => $conn
            ->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")
            ->fetchColumn(),
        "Registered patients" => $conn
            ->query("SELECT COUNT(*) FROM patients")
            ->fetchColumn(),
        "Pending appointments" => $conn
            ->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")
            ->fetchColumn(),
    ];
    $rows = $conn
        ->query(
            "SELECT p.name patient_name, d.name doctor_name,
                a.appointment_date, a.appointment_time, a.status
             FROM appointments a
             LEFT JOIN patients p ON p.id = a.patient_id
             LEFT JOIN doctors d ON d.id = a.doctor_id
             ORDER BY a.appointment_date ASC, a.appointment_time ASC
             LIMIT 8",
        )
        ->fetchAll();
    $columns = [
        "patient_name" => "Patient",
        "doctor_name" => "Doctor",
        "appointment_date" => "Date",
        "appointment_time" => "Time",
        "status" => "Status",
    ];
} elseif ($role === "pharmacist") {
    $stats = [
        "Pending prescriptions" => $conn
            ->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'Pending'")
            ->fetchColumn(),
        "Medicines in stock" => $conn
            ->query("SELECT COUNT(*) FROM medicines WHERE quantity > 0")
            ->fetchColumn(),
        "Low stock medicines" => $conn
            ->query("SELECT COUNT(*) FROM medicines WHERE quantity <= 10")
            ->fetchColumn(),
    ];
    $rows = $conn
        ->query(
            "SELECT p.medicine, p.dosage, p.status, p.created_at,
                    pt.name patient_name
             FROM prescriptions p
             LEFT JOIN patients pt ON pt.id = p.patient_id
             ORDER BY p.created_at DESC
             LIMIT 8",
        )
        ->fetchAll();
    $columns = [
        "medicine" => "Medicine",
        "dosage" => "Dosage",
        "patient_name" => "Patient",
        "status" => "Status",
        "created_at" => "Created",
    ];
    // Fetch low stock medicines for alerts
    $low_stock = $conn
        ->query("SELECT id, name, quantity, expiry_date FROM medicines WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 10")
        ->fetchAll();
} elseif ($role === "lab") {
    $stats = [
        "Pending tests" => $conn
            ->query("SELECT COUNT(*) FROM lab_tests WHERE status = 'Pending'")
            ->fetchColumn(),
        "Tests in progress" => $conn
            ->query("SELECT COUNT(*) FROM lab_tests WHERE status NOT IN ('Pending', 'Completed')")
            ->fetchColumn(),
        "Completed tests" => $conn
            ->query("SELECT COUNT(*) FROM lab_tests WHERE status = 'Completed'")
            ->fetchColumn(),
    ];
    $rows = $conn
        ->query(
            "SELECT l.test_name, l.status, p.name patient_name
             FROM lab_tests l
             LEFT JOIN patients p ON p.id = l.patient_id
             ORDER BY l.id DESC
             LIMIT 8",
        )
        ->fetchAll();
    $columns = [
        "test_name" => "Test",
        "patient_name" => "Patient",
        "status" => "Status",
    ];
} else {
    $stats = [
        "Total invoices" => $conn
            ->query("SELECT COUNT(*) FROM invoices")
            ->fetchColumn(),
        "Outstanding balance" => $conn
            ->query("SELECT COALESCE(SUM(i.total_amount - COALESCE(paid.total, 0)), 0) FROM invoices i LEFT JOIN (SELECT invoice_id, SUM(amount) total FROM billing_payments GROUP BY invoice_id) paid ON paid.invoice_id = i.id WHERE i.status IN ('Pending', 'Partial')")
            ->fetchColumn(),
        "Pending online payments" => $conn
            ->query("SELECT COUNT(*) FROM payment_requests WHERE status = 'Pending'")
            ->fetchColumn(),
        "Today's collections" => $conn
            ->query("SELECT COALESCE(SUM(amount), 0) FROM billing_payments WHERE DATE(payment_date) = CURDATE()")
            ->fetchColumn(),
    ];
    $rows = $conn
        ->query(
            "SELECT i.invoice_number, p.name patient_name, p.patient_code,
                i.total_amount, COALESCE(SUM(bp.amount), 0) paid_amount,
                i.total_amount - COALESCE(SUM(bp.amount), 0) balance,
                i.status, i.created_at
             FROM invoices i
             LEFT JOIN patients p ON p.id = i.patient_id
             LEFT JOIN billing_payments bp ON bp.invoice_id = i.id
             GROUP BY i.id, i.invoice_number, p.name, p.patient_code,
                  i.total_amount, i.status, i.created_at
             ORDER BY i.created_at DESC
             LIMIT 8",
        )
        ->fetchAll();
    $columns = [
        "invoice_number" => "Invoice",
        "patient_name" => "Patient name",
        "patient_code" => "Patient ID",
        "total_amount" => "Total",
        "paid_amount" => "Paid",
        "balance" => "Balance",
        "status" => "Status",
        "created_at" => "Created",
    ];
}
?>
<div class="page-actions">
    <div>
        <h2><?= e(ucfirst($role)) ?> dashboard</h2>
        <p class="muted">Review the latest work assigned to your department.</p>
    </div>
</div>

<div class="cards">
    <?php foreach ($stats as $label => $value): ?>
        <div class="card">
            <span><?= e($label) ?></span>
            <strong><?= in_array($label, ["Outstanding balance", "Today's collections"], true) ? "UGX " . number_format((float) $value) : e((string) $value) ?></strong>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($role === "pharmacist" && !empty($low_stock)): ?>
<div class="panel" style="border-left:4px solid #d9534f;background:#fff5f5;">
    <div class="panel-head">
        <h2 style="color:#d9534f;">⚠️ Low Stock Alert</h2>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Medicine</th>
                <th>Stock</th>
                <th>Expiry Date</th>
                <th></th>
            </tr>
            <?php foreach ($low_stock as $item): ?>
                <tr>
                    <td><?= e($item["name"]) ?></td>
                    <td style="color:#d9534f;font-weight:bold;"><?= e($item["quantity"]) ?> units</td>
                    <td><?= e($item["expiry_date"] ?: "—") ?></td>
                    <td><a class="mini-btn" href="<?= BASE_URL ?>/pharmacy/medicines.php?edit=<?= $item["id"] ?>">Reorder</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>Recent activity</h2>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <?php foreach ($columns as $heading): ?>
                    <th><?= e($heading) ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($columns as $field => $heading): ?>
                        <td><?= in_array($field, ["total_amount", "paid_amount", "balance"], true) ? "UGX " . number_format((float) ($row[$field] ?? 0)) : e($row[$field] ?? "") ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

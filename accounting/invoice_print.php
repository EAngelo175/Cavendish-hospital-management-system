<?php
$page_title = "Print Invoice";
require_once __DIR__ . "/../Includes/header.php";
require_role(["accountant", "receptionist", "patient"]);
$invoice_id = (int) ($_GET["id"] ?? 0);
$sql = "SELECT i.*, p.name patient_name, p.patient_code, p.email, p.phone
        FROM invoices i
        JOIN patients p ON p.id = i.patient_id
        WHERE i.id = ?";
$params = [$invoice_id];
if ($_SESSION["role"] === "patient") {
    $sql .= " AND i.patient_id = ?";
    $params[] = (int) $_SESSION["patient_id"];
}
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$invoice = $stmt->fetch();
if (!$invoice) exit("Invoice not found.");
$items = $conn->prepare("SELECT description, amount FROM invoice_items WHERE invoice_id = ? ORDER BY id");
$items->execute([$invoice_id]);
$items = $items->fetchAll();
$payments = $conn->prepare("SELECT amount, payment_method, reference_number, payment_date FROM billing_payments WHERE invoice_id = ? ORDER BY payment_date DESC");
$payments->execute([$invoice_id]);
$payments = $payments->fetchAll();
$paid = array_sum(array_column($payments, "amount"));
?>
<div class="page-actions no-print"><div><h2>Invoice <?= e($invoice["invoice_number"]) ?></h2><p class="muted">Printable billing statement.</p></div><button class="btn" type="button" onclick="window.print()">Print invoice</button></div>
<div class="print-sheet">
    <div class="invoice-heading"><div><img src="<?= HOSPITAL_LOGO ?>" alt="<?= e(HOSPITAL_NAME) ?> logo" width="58" height="58"><h1><?= e(HOSPITAL_NAME) ?></h1></div><div><strong>INVOICE</strong><br><?= e($invoice["invoice_number"]) ?><br><?= e($invoice["created_at"]) ?></div></div>
    <div class="invoice-parties"><div><small>BILLED TO</small><strong><?= e($invoice["patient_name"]) ?></strong><span><?= e($invoice["patient_code"]) ?></span><span><?= e($invoice["email"]) ?></span><span><?= e($invoice["phone"]) ?></span></div><div><small>STATUS</small><strong><?= e($invoice["status"]) ?></strong><span>Balance: UGX <?= number_format((float) $invoice["total_amount"] - $paid) ?></span></div></div>
    <table><tr><th>Description</th><th>Amount</th></tr><?php foreach ($items as $item): ?><tr><td><?= e($item["description"]) ?></td><td>UGX <?= number_format((float) $item["amount"]) ?></td></tr><?php endforeach; ?><tr><th>Total</th><th>UGX <?= number_format((float) $invoice["total_amount"]) ?></th></tr></table>
    <h3>Payments received</h3><table><tr><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th></tr><?php foreach ($payments as $payment): ?><tr><td><?= e($payment["payment_date"]) ?></td><td><?= e($payment["payment_method"]) ?></td><td><?= e($payment["reference_number"]) ?></td><td>UGX <?= number_format((float) $payment["amount"]) ?></td></tr><?php endforeach; ?></table>
</div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

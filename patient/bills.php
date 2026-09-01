<?php
$page_title = "My Bills";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$stmt = $conn->prepare("SELECT i.*, COALESCE(SUM(bp.amount), 0) paid_amount FROM invoices i LEFT JOIN billing_payments bp ON bp.invoice_id = i.id WHERE i.patient_id = ? GROUP BY i.id ORDER BY i.created_at DESC");
$stmt->execute([(int) $_SESSION["patient_id"]]);
$bills = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>My bills</h2><p class="muted">Review invoices and recorded payments.</p></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Invoice</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Date</th><th></th></tr><?php foreach ($bills as $bill): ?><tr><td><?= e($bill["invoice_number"]) ?></td><td>UGX <?= number_format((float) $bill["total_amount"]) ?></td><td>UGX <?= number_format((float) $bill["paid_amount"]) ?></td><td>UGX <?= number_format((float) $bill["total_amount"] - (float) $bill["paid_amount"]) ?></td><td><?= e($bill["status"]) ?></td><td><?= e($bill["created_at"]) ?></td><td><a class="mini-btn" href="../accounting/invoice_print.php?id=<?= $bill["id"] ?>">Print</a><?php if ((float) $bill["total_amount"] > (float) $bill["paid_amount"]): ?> <a class="mini-btn" href="pay.php?invoice_id=<?= $bill["id"] ?>">Pay</a><?php endif; ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

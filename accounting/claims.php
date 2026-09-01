<?php
$page_title = "Insurance Claims";
require_once __DIR__ . "/../Includes/header.php";
require_role("accountant");
$claims = $conn->query("SELECT c.*, i.invoice_number, ins.company_name FROM insurance_claims c JOIN insurance ins ON ins.id = c.insurance_id LEFT JOIN invoices i ON i.id = c.invoice_id ORDER BY c.created_at DESC")->fetchAll();
?>
<div class="page-actions"><div><h2>Insurance claims</h2><p class="muted">Track submitted insurance claims.</p></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Company</th><th>Invoice</th><th>Amount</th><th>Status</th><th>Created</th></tr><?php foreach ($claims as $claim): ?><tr><td><?= e($claim["company_name"]) ?></td><td><?= e($claim["invoice_number"]) ?></td><td>UGX <?= number_format((float) $claim["amount"]) ?></td><td><?= e($claim["claim_status"]) ?></td><td><?= e($claim["created_at"]) ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

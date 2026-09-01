<?php
$page_title = "Invoices";
require_once __DIR__ . "/../Includes/header.php";
require_role(["accountant", "receptionist"]);
$patients = $conn->query("SELECT id, patient_code, name FROM patients ORDER BY name")->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if ($_SESSION["role"] !== "accountant") {
		http_response_code(403);
		exit("Only accountants can create invoices.");
	}
	verify_csrf();
	$patient_id = (int) ($_POST["patient_id"] ?? 0);
	$description = trim($_POST["description"] ?? "");
	$amount = (float) ($_POST["amount"] ?? 0);
	if ($patient_id < 1 || $description === "" || $amount <= 0) {
		$error = "Select a patient and enter an item description and valid amount.";
	} else {
		try {
			$conn->beginTransaction();
			$invoice_number = "INV-" . date("Ymd-His");
			$stmt = $conn->prepare("INSERT INTO invoices (invoice_number, patient_id, total_amount, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
			$stmt->execute([$invoice_number, $patient_id, $amount]);
			$invoice_id = (int) $conn->lastInsertId();
			$stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, amount) VALUES (?, ?, ?)");
			$stmt->execute([$invoice_id, $description, $amount]);
			$conn->commit();
			header("Location: invoices.php");
			exit();
		} catch (PDOException $exception) {
			if ($conn->inTransaction()) {
				$conn->rollBack();
			}
			$error = "Could not create the invoice.";
		}
	}
}
$invoices = $conn->query("SELECT i.*, p.name patient_name, p.patient_code, (SELECT GROUP_CONCAT(CONCAT(ii.description, ' (UGX ', FORMAT(ii.amount, 0), ')') SEPARATOR ', ') FROM invoice_items ii WHERE ii.invoice_id = i.id) item_list, COALESCE(SUM(bp.amount), 0) paid_amount FROM invoices i LEFT JOIN patients p ON p.id = i.patient_id LEFT JOIN billing_payments bp ON bp.invoice_id = i.id GROUP BY i.id, p.name, p.patient_code ORDER BY i.created_at DESC")->fetchAll();
?>
<div class="page-actions"><div><h2>Invoices</h2><p class="muted">Review invoice balances and payment status.</p></div><button class="btn no-print" type="button" onclick="window.print()">Print report</button></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($_SESSION["role"] === "accountant"): ?><div class="panel"><h2>Create invoice</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): ?><option value="<?= $patient["id"] ?>"><?= e($patient["name"]) ?> - ID: <?= e($patient["patient_code"] ?: "Not assigned") ?></option><?php endforeach; ?></select></label><label>Item description<input name="description" value="Consulting doctor" placeholder="Consultation, laboratory test, medicine..." required></label><label>Amount (UGX)<input type="number" name="amount" value="100000" min="0.01" step="0.01" required></label><button class="btn">Create invoice</button></form></div><?php endif; ?>
<div class="panel"><div class="table-wrap"><table><tr><th>Invoice</th><th>Patient name</th><th>Patient ID</th><th>Items</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr><?php foreach ($invoices as $invoice): ?><tr><td><?= e($invoice["invoice_number"]) ?></td><td><?= e($invoice["patient_name"] ?: "Unknown patient") ?></td><td><?= e($invoice["patient_code"] ?: "Not assigned") ?></td><td><?= e($invoice["item_list"] ?: "No items") ?></td><td>UGX <?= number_format((float) $invoice["total_amount"]) ?></td><td>UGX <?= number_format((float) $invoice["paid_amount"]) ?></td><td>UGX <?= number_format((float) $invoice["total_amount"] - (float) $invoice["paid_amount"]) ?></td><td><?= e($invoice["status"]) ?></td><td><a class="mini-btn no-print" href="invoice_print.php?id=<?= $invoice["id"] ?>">Print</a></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

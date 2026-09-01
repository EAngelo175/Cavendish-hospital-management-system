<?php
$page_title = "Accountant Payments";
require_once __DIR__ . "/../Includes/header.php";
require_role("accountant");
$patients = $conn->query("SELECT id, patient_code, name FROM patients ORDER BY name")->fetchAll();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	verify_csrf();
	if (($_POST["action"] ?? "") === "approve_request") {
		$request_id = (int) $_POST["request_id"];
		$stmt = $conn->prepare("SELECT * FROM payment_requests WHERE id = ? AND status = 'Pending'");
		$stmt->execute([$request_id]);
		$request = $stmt->fetch();
		if ($request) {
			$conn->beginTransaction();
			$conn->prepare("INSERT INTO billing_payments (invoice_id, amount, payment_method, reference_number) VALUES (?, ?, ?, ?)")->execute([$request["invoice_id"], $request["amount"], $request["payment_method"], $request["reference_number"] ?: $request["card_last_four"]]);
			$conn->prepare("UPDATE payment_requests SET status = 'Approved' WHERE id = ?")->execute([$request_id]);
			$stmt = $conn->prepare("SELECT total_amount, (SELECT COALESCE(SUM(amount), 0) FROM billing_payments WHERE invoice_id = ?) paid FROM invoices WHERE id = ?");
			$stmt->execute([$request["invoice_id"], $request["invoice_id"]]);
			$balance = $stmt->fetch();
			$status = (float) $balance["paid"] >= (float) $balance["total_amount"] ? "Paid" : "Partial";
			$conn->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$status, $request["invoice_id"]]);
			$conn->commit();
		}
		header("Location: payments.php");
		exit();
	}
	$patient_id = (int) $_POST["patient_id"];
	$amount = (float) $_POST["amount"];
	$method = $_POST["payment_method"] ?? "Cash";
	$invoice_stmt = $conn->prepare("SELECT i.id, i.total_amount, COALESCE((SELECT SUM(amount) FROM billing_payments WHERE invoice_id = i.id), 0) paid_amount FROM invoices i WHERE i.patient_id = ? AND i.status IN ('Pending', 'Partial') ORDER BY i.created_at ASC, i.id ASC LIMIT 1");
	$invoice_stmt->execute([$patient_id]);
	$invoice = $invoice_stmt->fetch();
	$invoice_id = $invoice ? (int) $invoice["id"] : 0;
	$remaining = $invoice ? (float) $invoice["total_amount"] - (float) $invoice["paid_amount"] : 0;
	if ($patient_id < 1 || $invoice_id < 1) {
		$error = "This patient has no unpaid invoice.";
	} elseif ($amount <= 0 || $amount > $remaining || !in_array($method, ["Cash", "Mobile Money", "Card", "Bank", "Insurance"], true)) {
		$error = "Enter a valid amount and payment method.";
	} else {
		$stmt = $conn->prepare("INSERT INTO billing_payments (invoice_id, amount, payment_method, reference_number) VALUES (?, ?, ?, ?)");
		$stmt->execute([$invoice_id, $amount, $method, trim($_POST["reference_number"] ?? "") ?: null]);
		$stmt = $conn->prepare("SELECT total_amount, COALESCE((SELECT SUM(amount) FROM billing_payments WHERE invoice_id = ?), 0) paid FROM invoices WHERE id = ?");
		$stmt->execute([$invoice_id, $invoice_id]);
		$balance = $stmt->fetch();
		$status = (float) $balance["paid"] >= (float) $balance["total_amount"] ? "Paid" : "Partial";
		$conn->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$status, $invoice_id]);
		
		// Send payment confirmation email
		$patient_stmt = $conn->prepare("SELECT email, name, patient_code FROM patients WHERE id = ?");
		$patient_stmt->execute([$patient_id]);
		$patient_info = $patient_stmt->fetch();
		if ($patient_info && !empty($patient_info["email"])) {
			$invoice_stmt = $conn->prepare("SELECT invoice_number FROM invoices WHERE id = ?");
			$invoice_stmt->execute([$invoice_id]);
			$inv = $invoice_stmt->fetch();
			$balance_remaining = (float) $balance["total_amount"] - (float) $balance["paid"];
			$email_body = build_payment_confirmation_email(
				$patient_info["name"],
				$patient_info["patient_code"],
				$inv["invoice_number"],
				$amount,
				$method,
				$balance_remaining
			);
			send_payment_confirmation_email(
				$patient_info["email"],
				"Payment Confirmation - " . HOSPITAL_NAME,
				$email_body
			);
		}
		
		header("Location: payments.php");
		exit();
	}
}
$payments = $conn->query("SELECT bp.*, p.name patient_name, p.patient_code FROM billing_payments bp JOIN invoices i ON i.id = bp.invoice_id LEFT JOIN patients p ON p.id = i.patient_id ORDER BY bp.payment_date DESC")->fetchAll();
$requests = $conn->query("SELECT r.*, i.invoice_number, p.name patient_name, p.patient_code FROM payment_requests r JOIN invoices i ON i.id = r.invoice_id JOIN patients p ON p.id = r.patient_id WHERE r.status = 'Pending' ORDER BY r.created_at DESC")->fetchAll();
?>
<div class="page-actions"><div><h2>Payments</h2><p class="muted">View recorded collections and payment references.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<div class="panel"><h2>Record payment</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Patient<select name="patient_id" id="patient_id" required><option value="">Choose patient</option><?php foreach ($patients as $patient): ?><option value="<?= $patient["id"] ?>" data-name="<?= e($patient["name"]) ?>" data-code="<?= e($patient["patient_code"] ?: "Not assigned") ?>"><?= e($patient["name"]) ?> - ID: <?= e($patient["patient_code"] ?: "Not assigned") ?></option><?php endforeach; ?></select></label><label>Patient name<input id="patient_name" type="text" value="" placeholder="Select a patient" readonly></label><label>Patient ID<input id="patient_code" type="text" value="" placeholder="Select a patient" readonly></label><label>Amount<input type="number" name="amount" min="0.01" step="0.01" required></label><label>Method<select name="payment_method"><option>Cash</option><option>Mobile Money</option><option>Card</option><option>Bank</option><option>Insurance</option></select></label><label>Reference<input name="reference_number"></label><button class="btn">Record payment</button></form></div>
<script>
	const patientSelect = document.getElementById("patient_id");
	const patientName = document.getElementById("patient_name");
	const patientCode = document.getElementById("patient_code");
	patientSelect.addEventListener("change", () => {
		const selectedPatient = patientSelect.selectedOptions[0];
		patientName.value = selectedPatient?.dataset.name || "";
		patientCode.value = selectedPatient?.dataset.code || "";
	});
</script>
<div class="panel"><h2>Pending online payments</h2><div class="table-wrap"><table><tr><th>Invoice</th><th>Patient</th><th>Method</th><th>Amount</th><th>Reference</th><th></th></tr><?php foreach ($requests as $request): ?><tr><td><?= e($request["invoice_number"]) ?></td><td><?= e($request["patient_name"]) ?></td><td><?= e($request["payment_method"]) ?></td><td>UGX <?= number_format((float) $request["amount"]) ?></td><td><?= e($request["reference_number"] ?: "Card ending " . $request["card_last_four"]) ?></td><td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="approve_request"><input type="hidden" name="request_id" value="<?= $request["id"] ?>"><button class="mini-btn">Approve</button></form></td></tr><?php endforeach; ?></table></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient name</th><th>Patient ID</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date</th><th></th></tr><?php foreach ($payments as $payment): ?><tr><td><?= e($payment["patient_name"] ?: "Unknown patient") ?></td><td><?= e($payment["patient_code"] ?: "Not assigned") ?></td><td>UGX <?= number_format((float) $payment["amount"]) ?></td><td><?= e($payment["payment_method"]) ?></td><td><?= e($payment["reference_number"]) ?></td><td><?= e($payment["payment_date"]) ?></td><td><a class="mini-btn" href="payment_receipt.php?id=<?= $payment["id"] ?>">Receipt</a></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

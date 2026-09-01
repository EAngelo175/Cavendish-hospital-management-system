<?php
$page_title = "Make Payment";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$patient_id = (int) $_SESSION["patient_id"];
$invoice_id = (int) ($_GET["invoice_id"] ?? $_POST["invoice_id"] ?? 0);
$stmt = $conn->prepare("SELECT i.*, COALESCE(SUM(bp.amount), 0) paid_amount FROM invoices i LEFT JOIN billing_payments bp ON bp.invoice_id = i.id WHERE i.id = ? AND i.patient_id = ? GROUP BY i.id");
$stmt->execute([$invoice_id, $patient_id]);
$invoice = $stmt->fetch();
if (!$invoice) exit("Invoice not found.");
$balance = (float) $invoice["total_amount"] - (float) $invoice["paid_amount"];
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $method = $_POST["payment_method"] ?? "";
    $amount = (float) ($_POST["amount"] ?? 0);
    $reference = trim($_POST["reference_number"] ?? "");
    $last_four = trim($_POST["card_last_four"] ?? "");
    if (!in_array($method, ["Bank", "Card", "Mobile Money"], true) || $amount <= 0 || $amount > $balance) {
        $error = "Enter a valid amount and payment method.";
    } elseif ($method === "Card" && !preg_match("/^[0-9]{4}$/", $last_four)) {
        $error = "Enter the last four digits of your Visa card only.";
    } elseif ($method !== "Card" && $reference === "") {
        $error = "Enter the bank or mobile money transaction reference.";
    } else {
        $stmt = $conn->prepare("INSERT INTO payment_requests (invoice_id, patient_id, payment_method, amount, reference_number, card_last_four) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$invoice_id, $patient_id, $method, $amount, $reference ?: null, $last_four ?: null]);
        $success = "Payment submitted for verification by the accounts office.";
    }
}
?>
<div class="page-actions"><div><h2>Make a payment</h2><p class="muted">Invoice <?= e($invoice["invoice_number"]) ?> · Balance UGX <?= number_format($balance) ?></p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="invoice_id" value="<?= $invoice_id ?>"><label>Amount<input type="number" name="amount" min="0.01" max="<?= $balance ?>" step="0.01" required></label><label>Payment method<select name="payment_method" id="payment_method" required><option value="">Choose method</option><option>Bank</option><option>Card</option><option>Mobile Money</option></select></label><label id="reference_field">Transaction reference<input name="reference_number" placeholder="Bank or mobile money reference"></label><label id="card_field" hidden>Visa card last four digits<input name="card_last_four" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="Never enter the full card number"></label><button class="btn">Submit payment</button></form></div>
<script>
    const paymentMethod = document.getElementById("payment_method");
    const referenceField = document.getElementById("reference_field");
    const cardField = document.getElementById("card_field");
    paymentMethod.addEventListener("change", () => {
        const card = paymentMethod.value === "Card";
        referenceField.hidden = card;
        cardField.hidden = !card;
    });
</script>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

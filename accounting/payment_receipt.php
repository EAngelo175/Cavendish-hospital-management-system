<?php
$page_title = "Payment Receipt";
require_once __DIR__ . "/../Includes/header.php";
require_role(["accountant"]);

$payment_id = (int) ($_GET["id"] ?? 0);
$error = "";

if ($payment_id < 1) {
    $error = "Payment not found.";
} else {
    $stmt = $conn->prepare(
        "SELECT bp.*, i.invoice_number, i.total_amount, p.name patient_name, p.patient_code, p.phone, p.email 
         FROM billing_payments bp 
         JOIN invoices i ON i.id = bp.invoice_id 
         LEFT JOIN patients p ON p.id = i.patient_id 
         WHERE bp.id = ? 
         LIMIT 1"
    );
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        $error = "Payment not found.";
    }
}

if (isset($_GET["print"])) {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Payment Receipt</title><link rel="stylesheet" href="' . BASE_URL . '/assets/css/app.css"><link rel="stylesheet" href="' . BASE_URL . '/assets/css/print.css"></head><body><div class="print-sheet">';
    echo '<div style="max-width:600px;margin:0 auto;"><div style="text-align:center;margin-bottom:30px;"><h1 style="margin:0;color:#123047;">' . e(HOSPITAL_NAME) . '</h1><p style="margin:5px 0;color:#666;">Payment Receipt</p></div>';
    echo '<div style="border:1px solid #ddd;padding:20px;margin-bottom:20px;"><div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:15px;"><div><strong>Patient Name</strong><p>' . e($payment["patient_name"]) . '</p></div><div><strong>Patient ID</strong><p>' . e($payment["patient_code"]) . '</p></div><div><strong>Invoice Number</strong><p>' . e($payment["invoice_number"]) . '</p></div><div><strong>Payment Date</strong><p>' . e($payment["payment_date"]) . '</p></div></div></div>';
    echo '<div style="border:1px solid #ddd;padding:20px;margin-bottom:20px;"><h3 style="margin-top:0;color:#123047;">Payment Details</h3><div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;"><div><strong>Amount Paid</strong><p style="font-size:18px;color:#123047;font-weight:bold;">UGX ' . number_format((float) $payment["amount"]) . '</p></div><div><strong>Payment Method</strong><p>' . e($payment["payment_method"]) . '</p></div></div>';
    if ($payment["reference_number"]) {
        echo '<p><strong>Reference:</strong> ' . e($payment["reference_number"]) . '</p>';
    }
    echo '</div>';
    echo '<div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;"><p style="color:#666;font-size:12px;">This is an official payment receipt from ' . e(HOSPITAL_NAME) . '.<br>For inquiries, please contact the accounting department.</p></div>';
    echo '</div></div><script>window.print();</script></body></html>';
    exit();
}
?>
<div class="page-actions">
    <div>
        <h2>Payment Receipt</h2>
        <p class="muted">View and print payment confirmation.</p>
    </div>
    <?php if (!$error): ?>
        <a class="btn no-print" href="payment_receipt.php?id=<?= $payment_id ?>&print=1" target="_blank">Print receipt</a>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
    <div class="panel">
        <a class="btn" href="payments.php">Back to payments</a>
    </div>
<?php else: ?>
    <div class="panel" style="max-width:600px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:30px;">
            <h1 style="margin:0;color:#123047;"><?= e(HOSPITAL_NAME) ?></h1>
            <p style="margin:5px 0;color:#666;">Payment Receipt</p>
        </div>

        <div style="border:1px solid #ddd;padding:20px;margin-bottom:20px;background:#f9f9f9;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:15px;">
                <div>
                    <strong>Patient Name</strong>
                    <p><?= e($payment["patient_name"]) ?></p>
                </div>
                <div>
                    <strong>Patient ID</strong>
                    <p><?= e($payment["patient_code"]) ?></p>
                </div>
                <div>
                    <strong>Invoice Number</strong>
                    <p><?= e($payment["invoice_number"]) ?></p>
                </div>
                <div>
                    <strong>Payment Date</strong>
                    <p><?= e($payment["payment_date"]) ?></p>
                </div>
            </div>
        </div>

        <div style="border:1px solid #ddd;padding:20px;margin-bottom:20px;">
            <h3 style="margin-top:0;color:#123047;">Payment Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <strong>Amount Paid</strong>
                    <p style="font-size:18px;color:#123047;font-weight:bold;">UGX <?= number_format((float) $payment["amount"]) ?></p>
                </div>
                <div>
                    <strong>Payment Method</strong>
                    <p><?= e($payment["payment_method"]) ?></p>
                </div>
            </div>
            <?php if ($payment["reference_number"]): ?>
                <p style="margin-top:15px;">
                    <strong>Reference:</strong> <?= e($payment["reference_number"]) ?>
                </p>
            <?php endif; ?>
        </div>

        <div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;">
            <p style="color:#666;font-size:12px;">This is an official payment receipt from <?= e(HOSPITAL_NAME) ?>.<br>For inquiries, please contact the accounting department.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

<?php
$page_title = "Medical Records";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$patient_id = (int) ($_SESSION["patient_id"] ?? 0);
$patient_stmt = $conn->prepare("SELECT patient_code, name FROM patients WHERE id = ? LIMIT 1");
$patient_stmt->execute([$patient_id]);
$patient = $patient_stmt->fetch();
$records_stmt = $conn->prepare("SELECT r.*, d.name doctor_name FROM medical_records r LEFT JOIN doctors d ON d.id = r.doctor_id WHERE r.patient_id = ? ORDER BY r.created_at DESC");
$records_stmt->execute([$patient_id]);
$records = $records_stmt->fetchAll();
$lab_tests = $conn->prepare("SELECT test_name, status, result FROM lab_tests WHERE patient_id = ? ORDER BY id DESC");
$lab_tests->execute([$patient_id]);
$lab_tests = $lab_tests->fetchAll();
$prescriptions = $conn->prepare("SELECT medicine, dosage, instructions, status, created_at FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
$prescriptions->execute([$patient_id]);
$prescriptions = $prescriptions->fetchAll();
$bills = $conn->prepare("SELECT i.invoice_number, i.total_amount, i.status, i.created_at, COALESCE(SUM(bp.amount), 0) paid_amount, (SELECT GROUP_CONCAT(ii.description SEPARATOR ', ') FROM invoice_items ii WHERE ii.invoice_id = i.id) item_list FROM invoices i LEFT JOIN billing_payments bp ON bp.invoice_id = i.id WHERE i.patient_id = ? GROUP BY i.id, i.invoice_number, i.total_amount, i.status, i.created_at ORDER BY i.created_at DESC");
$bills->execute([$patient_id]);
$bills = $bills->fetchAll();
$patient_code = $patient["patient_code"] ?? "PAT-" . str_pad((string) $patient_id, 6, "0", STR_PAD_LEFT);

if (isset($_GET["print"])) {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Medical Record Print</title><link rel="stylesheet" href="' . BASE_URL . '/assets/css/app.css"><link rel="stylesheet" href="' . BASE_URL . '/assets/css/print.css"></head><body><div class="print-sheet"><div class="record-stack">';
    foreach ($records as $record) {
        echo '<article class="record-paper"><div class="record-header"><div><h3>Clinical Record</h3><p>' . e($record["created_at"]) . '</p></div><span class="record-badge">' . e($patient["name"] ?: "Patient") . '</span></div><div class="record-meta"><div><strong>Patient Name</strong><span>' . e($patient["name"] ?: "Patient") . '</span></div><div><strong>Patient ID</strong><span>' . e($patient_code) . '</span></div><div><strong>Record date</strong><span>' . e($record["created_at"]) . '</span></div></div><div class="record-body"><div class="record-block"><h4>Diagnosis</h4><p>' . nl2br(e($record["diagnosis"] ?: "—")) . '</p></div><div class="record-block"><h4>Clinical notes</h4><p>' . nl2br(e($record["notes"] ?: "—")) . '</p></div></div><div class="record-link-panel"><h4>Linked care</h4><p><strong>Lab:</strong> ' . (!empty($lab_tests) ? implode("; ", array_map(fn($test) => e($test["test_name"]) . ' (' . e($test["status"]) . ')', $lab_tests)) : 'No lab requests') . '</p><p><strong>Pharmacy:</strong> ' . (!empty($prescriptions) ? implode("; ", array_map(fn($item) => e($item["medicine"]) . ' - ' . e($item["dosage"]), $prescriptions)) : 'No prescriptions') . '</p><p><strong>Bills:</strong> ' . (!empty($bills) ? implode("; ", array_map(fn($bill) => e($bill["invoice_number"]) . ' (' . e($bill["status"]) . ')', $bills)) : 'No bills linked') . '</p></div><div class="record-signature"><div class="signature-line"></div><small>Doctor signature</small></div></article>';
    }
    echo '</div></div><script>window.print();</script></body></html>';
    exit();
}
?>
<div class="page-actions"><div><h2>Medical records</h2><p class="muted">Your diagnoses, clinical notes, and linked care history.</p></div><a class="btn no-print" href="records.php?print=1" target="_blank">Print records</a></div>
<?php if (empty($records)): ?>
<div class="panel empty-state">No medical records have been added yet.</div>
<?php else: ?>
<div class="record-stack">
    <?php foreach ($records as $record): ?>
        <article class="record-paper">
            <div class="record-header">
                <div>
                    <h3>Clinical Record</h3>
                    <p><?= e($record["created_at"]) ?></p>
                </div>
                <span class="record-badge"><?= e($patient["name"] ?: "Patient") ?></span>
            </div>

            <div class="record-meta">
                <div><strong>Patient Name</strong><span><?= e($patient["name"] ?: "Patient") ?></span></div>
                <div><strong>Patient ID</strong><span><?= e($patient_code) ?></span></div>
                <div><strong>Record date</strong><span><?= e($record["created_at"]) ?></span></div>
            </div>

            <div class="record-body">
                <div class="record-block">
                    <h4>Diagnosis</h4>
                    <p><?= nl2br(e($record["diagnosis"] ?: "—")) ?></p>
                </div>
                <div class="record-block">
                    <h4>Clinical notes</h4>
                    <p><?= nl2br(e($record["notes"] ?: "—")) ?></p>
                </div>
            </div>

            <div class="record-section">
                <h4>Linked care</h4>
                <div class="record-link-grid">
                    <div class="record-chip">
                        <strong>Seen by</strong>
                        <span><?= e($record["doctor_name"] ?: "Doctor") ?></span>
                    </div>
                    <div class="record-chip">
                        <strong>Lab</strong>
                        <span><?= empty($lab_tests) ? "No lab requests" : count($lab_tests) . " record(s)" ?></span>
                    </div>
                    <div class="record-chip">
                        <strong>Pharmacy</strong>
                        <span><?= empty($prescriptions) ? "No prescriptions" : count($prescriptions) . " item(s)" ?></span>
                    </div>
                    <div class="record-chip">
                        <strong>Bills</strong>
                        <span><?= empty($bills) ? "No bills" : count($bills) . " invoice(s)" ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($lab_tests) || !empty($prescriptions) || !empty($bills)): ?>
                <div class="record-details">
                    <?php if (!empty($lab_tests)): ?>
                        <div class="record-block">
                            <h4>Laboratory tests</h4>
                            <ul>
                                <?php foreach ($lab_tests as $test): ?>
                                    <li><?= e($test["test_name"]) ?> — <?= e($test["status"]) ?><?= $test["result"] ? " — " . e($test["result"]) : "" ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($prescriptions)): ?>
                        <div class="record-block">
                            <h4>Pharmacy</h4>
                            <ul>
                                <?php foreach ($prescriptions as $item): ?>
                                    <li><?= e($item["medicine"]) ?> — <?= e($item["dosage"]) ?><?= $item["instructions"] ? " — " . e($item["instructions"]) : "" ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($bills)): ?>
                        <div class="record-block">
                            <h4>Bills</h4>
                            <ul>
                                <?php foreach ($bills as $bill): ?>
                                    <li><?= e($bill["invoice_number"]) ?> — <?= e($bill["status"]) ?> — UGX <?= number_format((float) $bill["total_amount"]) ?><?= $bill["item_list"] ? " — " . e($bill["item_list"]) : "" ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="record-signature">
                <div class="signature-line"></div>
                <small>Doctor signature</small>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

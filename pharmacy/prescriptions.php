<?php
$page_title = "Pharmacy Prescriptions";
require_once __DIR__ . "/../Includes/header.php";
require_role("pharmacist");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $status = $_POST["status"] ?? "Pending";
    $prescription_id = (int) $_POST["id"];
    if ($status === "Dispensed") {
        $stmt = $conn->prepare("SELECT medicine, medicine_id FROM prescriptions WHERE id = ?");
        $stmt->execute([$prescription_id]);
        $prescription = $stmt->fetch();
        if ($prescription && $prescription["medicine_id"]) {
            $stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - 1 WHERE id = ? AND quantity > 0");
            $stmt->execute([(int) $prescription["medicine_id"]]);
            if (!$stmt->rowCount()) $status = "Pending";
        }
    }
    if (in_array($status, ["Pending", "Dispensed", "Cancelled"], true)) {
        $stmt = $conn->prepare("UPDATE prescriptions SET status = ? WHERE id = ?");
        $stmt->execute([$status, $prescription_id]);
    }
    header("Location: prescriptions.php");
    exit();
}
$prescriptions = $conn
    ->query("SELECT r.*, p.name patient_name FROM prescriptions r LEFT JOIN patients p ON p.id = r.patient_id ORDER BY r.created_at DESC")
    ->fetchAll();
?>
<div class="page-actions"><div><h2>Prescription queue</h2><p class="muted">Review and dispense prescribed medicines.</p></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Medicine</th><th>Dosage</th><th>Instructions</th><th>Status</th><th>Update</th></tr><?php foreach ($prescriptions as $prescription): ?><tr><td><?= e($prescription["patient_name"]) ?></td><td><?= e($prescription["medicine"]) ?></td><td><?= e($prescription["dosage"]) ?></td><td><?= e($prescription["instructions"]) ?></td><td><?= e($prescription["status"]) ?></td><td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $prescription["id"] ?>"><select name="status"><option>Pending</option><option>Dispensed</option><option>Cancelled</option></select><button class="mini-btn">Save</button></form></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

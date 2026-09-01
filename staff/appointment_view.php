<?php
$page_title = "Approve Appointment";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");
$id = (int) ($_GET["id"] ?? 0);
$stmt = $conn->prepare("SELECT a.*, p.name patient_name, p.user_id patient_user_id, d.name doctor_name, u.id doctor_user_id FROM appointments a LEFT JOIN patients p ON p.id = a.patient_id LEFT JOIN doctors d ON d.id = a.doctor_id LEFT JOIN users u ON u.doctor_id = a.doctor_id WHERE a.id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch();
if (!$appointment) exit("Appointment not found.");

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $status = $_POST["status"] ?? "";
    $cancellation_reason = trim($_POST["cancellation_reason"] ?? "");
    
    if (!in_array($status, ["Pending", "Approved", "Cancelled", "Rescheduled"], true)) {
        $error = "Invalid status.";
    } elseif ($status === "Cancelled" && $cancellation_reason === "") {
        $error = "Please provide a reason for cancellation.";
    } else {
        $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$status, $id]);
        
        if ($status === "Cancelled") {
            $message = "Appointment on " . $appointment["appointment_date"] . " at " . $appointment["appointment_time"] . " has been cancelled. Reason: " . $cancellation_reason;
        } else {
            $message = "Appointment on " . $appointment["appointment_date"] . " at " . $appointment["appointment_time"] . " is now " . $status . ".";
        }
        
        foreach (array_unique(array_filter([(int) $appointment["patient_user_id"], (int) $appointment["doctor_user_id"]])) as $recipient_id) {
            $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$recipient_id, "Appointment update", $message]);
        }
        
        audit($conn, "Updated appointment status to " . $status, "appointments");
        header("Location: appointment_view.php?id=$id");
        exit();
    }
}
?>
<div class="page-actions"><div><h2>Appointment approval</h2><p class="muted">Review booking <?= e((string) $appointment["id"]) ?>.</p></div></div><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><div class="grid two"><div class="panel"><h2>Booking details</h2><p><b>Patient:</b> <?= e($appointment["patient_name"] ?? $appointment["patient_name"]) ?></p><p><b>Doctor:</b> <?= e($appointment["doctor_name"] ?? "") ?></p><p><b>Date:</b> <?= e($appointment["appointment_date"]) ?></p><p><b>Time:</b> <?= e($appointment["appointment_time"]) ?></p><p><b>Reason:</b> <?= e($appointment["reason"]) ?></p><p><b>Status:</b> <span style="background:#e8f4f8;padding:4px 8px;border-radius:4px;color:#123047;font-weight:bold;"><?= e($appointment["status"]) ?></span></p></div><div class="panel"><h2>Set status</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Status<select name="status" id="status_field"><option value="Pending" <?= $appointment["status"] === "Pending" ? "selected" : "" ?>>Pending</option><option value="Approved" <?= $appointment["status"] === "Approved" ? "selected" : "" ?>>Approved</option><option value="Cancelled" <?= $appointment["status"] === "Cancelled" ? "selected" : "" ?>>Cancelled</option><option value="Rescheduled" <?= $appointment["status"] === "Rescheduled" ? "selected" : "" ?>>Rescheduled</option></select></label><div id="cancellation_section" style="display:none;"><label>Cancellation Reason<textarea name="cancellation_reason" placeholder="Explain why..."></textarea></label></div><button class="btn">Save status</button></form></div></div><script>const statusField=document.getElementById("status_field");const cancelSection=document.getElementById("cancellation_section");statusField.addEventListener("change",()=>{cancelSection.style.display=statusField.value==="Cancelled"?"block":"none"});statusField.dispatchEvent(new Event("change"));</script>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

<?php $page_title = "Appointment";
require_once __DIR__ . "/../Includes/header.php";
require_role("doctor");
$id = (int) $_GET["id"];
$s = $conn->prepare(
    "SELECT a.*,p.id patient_id,p.name patient_name,p.phone,p.email,d.name doctor_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id WHERE a.id=? AND a.doctor_id=?",
);
$s->execute([$id, $_SESSION["doctor_id"]]);
$a = $s->fetch();
if (!$a) {
    exit("Appointment not found.");
}
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $status = $_POST["status"];
    $cancellation_reason = trim($_POST["cancellation_reason"] ?? "");
    
    // If cancelling, reason is required
    if ($status === "Cancelled" && $cancellation_reason === "") {
        $error = "Please provide a reason for cancellation.";
    } else {
        $conn->prepare("UPDATE appointments SET status=? WHERE id=?")->execute([$status, $id]);
        $u = $conn->prepare("SELECT user_id FROM patients WHERE id=?");
        $u->execute([$a["patient_id"]]);
        if ($uid = $u->fetchColumn()) {
            $n = $conn->prepare("INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)");
            
            if ($status === "Cancelled") {
                $n->execute([$uid, "Appointment Cancelled", "Your appointment on " . $a["appointment_date"] . " at " . $a["appointment_time"] . " has been cancelled. Reason: " . $cancellation_reason]);
            } else {
                $n->execute([$uid, "Appointment update", "Your appointment on " . $a["appointment_date"] . " at " . $a["appointment_time"] . " is now " . $status . "."]);
            }

            if ($status === "Approved") {
                $patient_code = trim((string) ($a["patient_code"] ?? ""));
                $patient_name = trim((string) ($a["patient_name"] ?? ""));
                $verification_email = trim((string) ($a["email"] ?? ""));
                $n->execute([$uid, "Verification required", "Your appointment has been approved. Please verify your details before the consultation."]);
                if ($verification_email !== "") {
                    send_patient_verification_email($verification_email, "Appointment Approval Verification - " . HOSPITAL_NAME, build_patient_verification_email($patient_name, $patient_code, (string) $a["doctor_name"], (string) $a["appointment_date"], (string) $a["appointment_time"]));
                }
            }
        }
        header("Location: appointment_view.php?id=$id");
        exit();
    }
}
?><div class="page-actions"><div><h2>Appointment</h2><p class="muted"><?= e($a["patient_name"]) ?></p></div></div><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><div class="grid two"><div class="panel"><h2>Patient</h2><p>Phone: <?= e($a["phone"]) ?></p><p>Email: <?= e($a["email"]) ?></p><p>Reason: <?= e($a["reason"]) ?></p><p>Date: <?= e($a["appointment_date"]) ?> <?= e($a["appointment_time"]) ?></p></div><div class="panel"><h2>Update status</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Status<select name="status" id="status_select"><option <?= $a["status"] === "Pending" ? "selected" : "" ?>>Pending</option><option <?= $a["status"] === "Approved" ? "selected" : "" ?>>Approved</option><option <?= $a["status"] === "Completed" ? "selected" : "" ?>>Completed</option><option <?= $a["status"] === "Cancelled" ? "selected" : "" ?>>Cancelled</option><option <?= $a["status"] === "Rescheduled" ? "selected" : "" ?>>Rescheduled</option></select></label><div id="cancellation_reason_field" style="display:none;"><label>Cancellation reason<textarea name="cancellation_reason" placeholder="Provide a reason for cancellation..."></textarea></label></div><button class="btn">Save</button></form></div></div><script>document.getElementById("status_select").addEventListener("change", function() { document.getElementById("cancellation_reason_field").style.display = this.value === "Cancelled" ? "block" : "none"; });document.getElementById("status_select").dispatchEvent(new Event("change"));</script><?php require_once __DIR__ . "/../Includes/footer.php"; ?>

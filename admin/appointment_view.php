<?php $page_title = "Manage Appointment";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$id = (int) $_GET["id"];
$q = $conn->prepare(
    "SELECT a.*,p.name patient_name,p.phone patient_phone,d.name doctor_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id WHERE a.id=?",
);
$q->execute([$id]);
$a = $q->fetch();
if (!$a) {
    exit("Appointment not found.");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $status = $_POST["status"];
    $q = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
    $q->execute([$status, $id]);
    audit(
        $conn,
        "Changed appointment #" . $id . " to " . $status,
        "appointments",
    );
    $q = $conn->prepare("SELECT user_id FROM patients WHERE id=?");
    $q->execute([$a["patient_id"]]);
    $uid = $q->fetchColumn();
    if ($uid) {
        $n = $conn->prepare(
            "INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)",
        );
        $n->execute([
            $uid,
            "Appointment update",
            "Your appointment on " .
            $a["appointment_date"] .
            " at " .
            $a["appointment_time"] .
            " is now " .
            $status .
            ".",
        ]);
    }
    header("Location: appointment_view.php?id=$id");
    exit();
}
?><div class="grid two"><div class="panel"><h2>Booking</h2><p><b>Patient:</b> <?= e(
    $a["patient_name"],
) ?></p><p><b>Phone:</b> <?= e(
    $a["patient_phone"],
) ?></p><p><b>Doctor:</b> <?= e($a["doctor_name"]) ?></p><p><b>Date:</b> <?= e(
    $a["appointment_date"],
) ?></p><p><b>Time:</b> <?= e(
    $a["appointment_time"],
) ?></p><p><b>Reason:</b> <?= e(
    $a["reason"],
) ?></p></div><div class="panel"><h2>Update status</h2><form method="post"><?= csrf_field() ?><label>Status<select name="status"><?php foreach (
    ["Pending", "Approved", "Completed", "Cancelled", "Rescheduled"]
    as $x
): ?><option <?= $a["status"] === $x
    ? "selected"
    : "" ?>><?= $x ?></option><?php endforeach; ?></select></label><br><button class="btn">Save status</button></form></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

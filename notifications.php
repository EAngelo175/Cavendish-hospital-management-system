<?php
$page_title = "Notifications";
require_once __DIR__ . "/Includes/header.php";
require_role(["admin", "doctor", "patient", "receptionist", "pharmacist", "lab", "accountant"]);
$user_id = (int) $_SESSION["user_id"];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([(int) $_POST["id"], $user_id]);
    header("Location: notifications.php");
    exit();
}
$stmt = $conn->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
$unread_notifications = array_filter($notifications, fn($notification) => (int) $notification["is_read"] === 0);
?>
<div class="page-actions"><div><h2>Notifications</h2><p class="muted">Updates about your hospital activity.</p></div></div>
<div class="panel"><div class="notification-summary"><?= count($unread_notifications) ?> unread notification<?= count($unread_notifications) === 1 ? "" : "s" ?></div><?php if (empty($notifications)): ?><p class="muted">No notifications yet.</p><?php else: ?><div class="table-wrap"><table><tr><th>Message</th><th>Date</th><th>Status</th><th></th></tr><?php foreach ($notifications as $notification): ?><tr><td><strong><?= e($notification["title"]) ?></strong><br><?= e($notification["message"]) ?></td><td><?= e($notification["created_at"]) ?></td><td><?= $notification["is_read"] ? "Read" : "Unread" ?></td><td><?php if (!$notification["is_read"]): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $notification["id"] ?>"><button class="mini-btn">Mark read</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></div><?php endif; ?></div>
<?php require_once __DIR__ . "/Includes/footer.php"; ?>

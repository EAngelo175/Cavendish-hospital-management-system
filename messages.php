<?php
$page_title = "Messages";
require_once __DIR__ . "/Includes/header.php";
require_role(["admin", "doctor", "receptionist", "pharmacist", "lab", "accountant"]);
$user_id = (int) $_SESSION["user_id"];
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $recipient_id = (int) ($_POST["recipient_id"] ?? 0);
    $subject = trim($_POST["subject"] ?? "");
    $body = trim($_POST["body"] ?? "");
    if (!$recipient_id || $recipient_id === $user_id || $subject === "" || $body === "") {
        $error = "Choose a recipient and complete the message.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $recipient_id, $subject, $body]);
        $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)")->execute([$recipient_id, "New message", $subject]);
        header("Location: messages.php?sent=1");
        exit();
    }
}
$people = $conn->prepare("SELECT id, username, role FROM users WHERE id <> ? AND role <> 'patient' ORDER BY username");
$people->execute([$user_id]);
$recipients = $people->fetchAll();
$stmt = $conn->prepare("SELECT m.*, u.username sender_name FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.recipient_id = ? OR m.sender_id = ? ORDER BY m.created_at DESC LIMIT 50");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>Messages</h2><p class="muted">Communicate securely with hospital users.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if (isset($_GET["sent"])): ?><div class="alert success">Message sent.</div><?php endif; ?>
<div class="grid two"><div class="panel"><h2>New message</h2><form method="post" class="form-grid"><?= csrf_field() ?><label>Recipient<select name="recipient_id" required><option value="">Choose user</option><?php foreach ($recipients as $recipient): ?><option value="<?= $recipient["id"] ?>"><?= e($recipient["username"] . " (" . $recipient["role"] . ")") ?></option><?php endforeach; ?></select></label><label>Subject<input name="subject" required></label><label>Message<textarea name="body" rows="6" required></textarea></label><button class="btn">Send message</button></form></div><div class="panel"><h2>Conversation activity</h2><?php foreach ($messages as $message): ?><article><strong><?= e($message["subject"]) ?></strong><p><?= e($message["body"]) ?></p><small><?= e($message["sender_name"]) ?> · <?= e($message["created_at"]) ?></small></article><?php endforeach; ?></div></div>
<?php require_once __DIR__ . "/Includes/footer.php"; ?>

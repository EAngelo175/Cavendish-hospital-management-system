<?php
$page_title = "Verify Password Change";
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/Includes/header.php";

$token = trim($_GET["token"] ?? "");
$error = "";
$success = "";

if ($token === "") {
    $error = "Invalid verification link.";
} else {
    // Check if token exists and is not expired
    $stmt = $conn->prepare(
        "SELECT id, username, email, pending_password, password_reset_expires 
         FROM users 
         WHERE password_reset_token = ? AND password_reset_expires > NOW() 
         LIMIT 1"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "This verification link is invalid or has expired.";
    } else {
        // Apply the password change
        $update_stmt = $conn->prepare(
            "UPDATE users 
             SET password = ?, 
                 pending_password = NULL, 
                 password_reset_token = NULL, 
                 password_reset_expires = NULL 
             WHERE id = ?"
        );
        $update_stmt->execute([$user["pending_password"], $user["id"]]);
        
        // Log out the user from all sessions
        session_destroy();
        
        $success = "Your password has been successfully changed. You will be redirected to login in a moment.";
        // Don't log audit since session is destroyed
    }
}
?>
<div class="page-actions">
    <div>
        <h2>Password Change Verification</h2>
        <p class="muted">Verify your password change request.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
    <div class="panel">
        <p>If your link has expired, you can request another verification email by visiting your profile and changing your password again.</p>
        <a class="btn" href="<?= BASE_URL ?>/profile.php">Back to Profile</a>
    </div>
<?php elseif ($success): ?>
    <div class="alert success"><?= e($success) ?></div>
    <div class="panel">
        <p>You will be redirected to the login page in a moment...</p>
        <script>
            setTimeout(() => {
                window.location.href = "<?= BASE_URL ?>/login.php";
            }, 3000);
        </script>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . "/Includes/footer.php"; ?>

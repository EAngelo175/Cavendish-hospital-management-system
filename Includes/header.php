<?php
require_once __DIR__ . "/../config/app.php";
$current_role = $_SESSION["role"] ?? "";
$notification_count = 0;
if (!empty($_SESSION["user_id"])) {
    $notification_stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $notification_stmt->execute([(int) $_SESSION["user_id"]]);
    $notification_count = (int) $notification_stmt->fetchColumn();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($page_title ?? HOSPITAL_NAME) ?> | <?= e(HOSPITAL_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/print.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . "/sidebar.php"; ?>
    <main class="main-content">
        <header class="topbar">
            <div>
                <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false">Menu</button>
                <strong><?= e(HOSPITAL_NAME) ?></strong>
            </div>
            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/notifications.php" class="notification-link">Notifications<?php if ($notification_count > 0): ?><span class="notification-badge"><?= $notification_count > 99 ? "99+" : $notification_count ?></span><?php endif; ?></a><?php if ($current_role !== "patient"): ?><a href="<?= BASE_URL ?>/profile.php"><?= e(
    $_SESSION["username"] ?? "Guest",
) ?></a><?php else: ?><span><?= e(
    $_SESSION["username"] ?? "Guest",
) ?></span><?php endif; ?></div><div class="top-user"><?= e(
    $_SESSION["username"] ?? "Guest",
) ?></div>
    </header>
    <section class="content">
    <?php
    // Show session timeout warning if user is about to timeout
    if (isset($_SESSION["user_id"]) && isset($_SESSION["last_activity"])) {
        $time_remaining = SESSION_TIMEOUT - (time() - $_SESSION["last_activity"]);
        if ($time_remaining > 0 && $time_remaining < 300) { // Warning in last 5 minutes
            $minutes_left = ceil($time_remaining / 60);
            echo '<div class="alert" style="background:#fff3cd;border:1px solid #ffc107;color:#856404;margin:12px;padding:12px;border-radius:6px;">';
            echo '<strong>⏱️ Session Timeout Warning:</strong> Your session will expire in ' . $minutes_left . ' minute' . ($minutes_left !== 1 ? 's' : '') . '. <a href="' . BASE_URL . '/profile.php" style="color:#856404;text-decoration:underline;">Refresh your session</a> or you will be logged out.';
            echo '</div>';
        }
    }
    ?>

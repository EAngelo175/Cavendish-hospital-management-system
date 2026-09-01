<?php
require_once __DIR__ . "/config/app.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$error = "";
$success = isset($_GET["registered"])
    ? "Registration successful. You can now sign in."
    : (isset($_GET["timeout"]) ? "Your session has expired. Please log in again." : "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";
    $query = $conn->prepare(
        "SELECT u.*, p.id patient_id
         FROM users u
         LEFT JOIN patients p ON p.user_id = u.id
         WHERE u.username = ? OR u.email = ?
         LIMIT 1",
    );
    $query->execute([$login, $login]);
    $user = $query->fetch();

    if ($user && password_verify($password, $user["password"])) {
        login_user($user);
        header("Location: " . BASE_URL . "/dashboard.php");
        exit();
    }

    $error = "Invalid username/email or password.";
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e('CAVENDISH INTERNATIONAL HOSPITAL') ?> Login</title>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    </head>
    <body class="login-page">
        <div class="login-card">
            <img src="<?= e(HOSPITAL_LOGO) ?>" alt="<?= e(HOSPITAL_NAME) ?> logo" width="75" height="75">
            <h1><?= e('CAVENDISH INTERNATIONAL HOSPITAL') ?></h1>
            <p>Secure portal access</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success"><?= e($success) ?></div>
            <?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <label>
                    Username or email
                    <input name="login" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" required>
                </label>
                <button class="btn">Sign in</button>
            </form>

            <a href="<?= BASE_URL ?>/patient/register.php">Patient? Create an account</a>
        </div>
    </body>
</html>

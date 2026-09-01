<?php
$page_title = "Patient Registration";
require_once __DIR__ . "/../config/app.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $gender = $_POST["gender"] ?? "Male";
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $dob = $_POST["dob"] ?? null;
    $age = (int) ($_POST["age"] ?? 0);
    $blood_group = trim($_POST["blood_group"] ?? "");

    if ($name === "" || $username === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Name, username and a valid email are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($age < 0 || $age > 130) {
        $error = "Please enter a valid age.";
    } elseif (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $error = "Please select a valid gender.";
    } else {
        try {
            $conn->beginTransaction();

            $user_query = $conn->prepare(
                "INSERT INTO users (username, email, password, role)
                 VALUES (?, ?, ?, 'patient')",
            );
            $user_query->execute([
                $username,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $user_id = (int) $conn->lastInsertId();

            $patient_query = $conn->prepare(
                "INSERT INTO patients
                          (name, gender, age, dob, phone, address, blood_group, email, user_id)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            );
            $patient_query->execute([
                $name,
                $gender,
                $age,
                $dob ?: null,
                $phone ?: null,
                $address ?: null,
                $blood_group ?: null,
                $email,
                $user_id,
            ]);
            $patient_id = (int) $conn->lastInsertId();
            $patient_code = "PAT-" . str_pad((string) $patient_id, 6, "0", STR_PAD_LEFT);
            $conn->prepare("UPDATE patients SET patient_code = ? WHERE id = ?")
                ->execute([$patient_code, $patient_id]);

            $conn->commit();
            header("Location: " . BASE_URL . "/login.php?registered=1");
            exit();
        } catch (PDOException $exception) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = "Username or email is already registered.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Patient Registration</title>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    </head>
    <body class="login-page">
        <div class="login-card">
            <h1>Create patient account</h1>
            <p>Register to book and manage appointments.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form-grid">
                <?= csrf_field() ?>
                <label>
                    Full name
                    <input name="name" value="<?= e($_POST["name"] ?? "") ?>" required>
                </label>
                <label>
                    Username
                    <input name="username" value="<?= e($_POST["username"] ?? "") ?>" required>
                </label>
                <label>
                    Email
                    <input type="email" name="email" value="<?= e($_POST["email"] ?? "") ?>" required>
                </label>
                <label>
                    Gender
                    <select name="gender">
                        <?php foreach (["Male", "Female", "Other"] as $option): ?>
                            <option <?= ($_POST["gender"] ?? "Male") === $option ? "selected" : "" ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Date of birth
                    <input type="date" name="dob" value="<?= e($_POST["dob"] ?? "") ?>">
                </label>
                <label>
                    Age
                    <input type="number" name="age" min="0" max="130" value="<?= e($_POST["age"] ?? "") ?>" required>
                </label>
                <label>
                    Phone
                    <input name="phone" value="<?= e($_POST["phone"] ?? "") ?>">
                </label>
                <label>
                    Blood group
                    <input name="blood_group" value="<?= e($_POST["blood_group"] ?? "") ?>">
                </label>
                <label>
                    Address
                    <textarea name="address"><?= e($_POST["address"] ?? "") ?></textarea>
                </label>
                <label>
                    Password
                    <input type="password" name="password" minlength="8" required>
                </label>
                <label>
                    Confirm password
                    <input type="password" name="confirm_password" minlength="8" required>
                </label>
                <div class="form-actions">
                    <button class="btn">Create account</button>
                    <a class="btn secondary" href="<?= BASE_URL ?>/login.php">Back to login</a>
                </div>
            </form>
        </div>
    </body>
</html>

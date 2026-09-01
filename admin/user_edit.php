<?php
$page_title = "Edit User";
require_once __DIR__ . "/../Includes/header.php";
require_role("admin");

$id = (int) ($_GET["id"] ?? 0);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    exit("User not found.");
}

$roles = ["admin", "doctor", "receptionist", "pharmacist", "lab", "accountant", "patient"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $email = trim($_POST["email"] ?? "");
    $role = $_POST["role"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, $roles, true)) {
        $error = "Enter a valid email and role.";
    } elseif ($password !== "" && strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        try {
            $sql = "UPDATE users SET email = ?, role = ?";
            $params = [$email, $role];
            if ($password !== "") {
                $sql .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            audit($conn, "Updated user " . $user["username"], "users");
            header("Location: users.php");
            exit();
        } catch (PDOException $exception) {
            $error = "That email address is already in use.";
        }
    }
}
?>
<div class="page-actions">
    <div>
        <h2>Edit user</h2>
        <p class="muted">Update the account for <?= e($user["username"]) ?>.</p>
    </div>
</div>

<div class="panel">
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>
            Username
            <input value="<?= e($user["username"]) ?>" disabled>
        </label>
        <label>
            Email
            <input type="email" name="email" value="<?= e($user["email"]) ?>" required>
        </label>
        <label>
            Role
            <select name="role" id="role" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= e($role) ?>" <?= $user["role"] === $role ? "selected" : "" ?>><?= e(ucfirst($role)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            New password
            <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password">
        </label>
        <div class="form-actions">
            <button class="btn">Save changes</button>
            <a class="btn secondary" href="users.php">Cancel</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

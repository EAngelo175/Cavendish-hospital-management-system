<?php
$page_title = "Create User";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $u = trim($_POST["username"]);
    $e = trim($_POST["email"]);
    $pw = $_POST["password"];
    $r = $_POST["role"];
    if (
        $u === "" ||
        !filter_var($e, FILTER_VALIDATE_EMAIL) ||
        strlen($pw) < 8
    ) {
        $error =
            "Username, valid email and password (8+ characters) are required.";
    } else {
        try {
            $s = $conn->prepare(
                "INSERT INTO users(username,email,password,role) VALUES(?,?,?,?)",
            );
            $s->execute([$u, $e, password_hash($pw, PASSWORD_DEFAULT), $r]);
            audit($conn, "Created user " . $u, "users");
            header("Location: users.php");
            exit();
        } catch (PDOException $x) {
            $error = "Username or email already exists.";
        }
    }
}
?><div class="page-actions"><div><h2>Create user</h2></div></div><?php if (
    $error
): ?><div class="alert error"><?= e(
    $error,
) ?></div><?php endif; ?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Username<input name="username" required></label><label>Email<input type="email" name="email" required></label><label>Role<select name="role" id="role"><?php foreach (
    [
        "admin",
        "doctor",
        "receptionist",
        "pharmacist",
        "lab",
        "accountant",
        "patient",
    ]
    as $r
): ?><option><?= $r ?></option><?php endforeach; ?></select></label><label>Password<input type="password" name="password" minlength="8" required></label><div class="form-actions"><button class="btn">Create</button><a class="btn secondary" href="users.php">Cancel</a></div></form></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

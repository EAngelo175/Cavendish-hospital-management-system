<?php $page_title = "Add Doctor";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $n = trim($_POST["name"]);
    $s = trim($_POST["specialization"]);
    $p = trim($_POST["phone"]);
    $e = trim($_POST["email"]);
    if ($n === "" || $s === "") {
        $error = "Name and specialization are required.";
    } else {
        try {
            $q = $conn->prepare(
                "INSERT INTO doctors(name,specialization,phone,email) VALUES(?,?,?,?)",
            );
            $q->execute([$n, $s, $p, $e ?: null]);
            audit($conn, "Created doctor " . $n, "doctors");
            header("Location: doctors.php");
            exit();
        } catch (PDOException $x) {
            $error = "Could not create doctor.";
        }
    }
}
if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif;
?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Name<input name="name" required></label><label>Specialization<input name="specialization" required></label><label>Phone<input name="phone"></label><label>Email<input type="email" name="email"></label><div class="form-actions"><button class="btn">Save doctor</button><a class="btn secondary" href="doctors.php">Cancel</a></div></form></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

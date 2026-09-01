<?php $page_title = "Edit Doctor";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$id = (int) $_GET["id"];
$q = $conn->prepare("SELECT * FROM doctors WHERE id=?");
$q->execute([$id]);
$d = $q->fetch();
$error = "";
if (!$d) {
    exit("Doctor not found.");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $n = trim($_POST["name"]);
    $s = trim($_POST["specialization"]);
    $p = trim($_POST["phone"]);
    $e = trim($_POST["email"]);
    if ($n === "" || $s === "") {
        $error = "Name and specialization are required.";
    } else {
        $q = $conn->prepare(
            "UPDATE doctors SET name=?,specialization=?,phone=?,email=? WHERE id=?",
        );
        $q->execute([$n, $s, $p, $e ?: null, $id]);
        audit($conn, "Updated doctor " . $n, "doctors");
        header("Location: doctors.php");
        exit();
    }
}
?><div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Name<input name="name" value="<?= e(
    $d["name"],
) ?>" required></label><label>Specialization<input name="specialization" value="<?= e(
    $d["specialization"],
) ?>" required></label><label>Phone<input name="phone" value="<?= e(
    $d["phone"],
) ?>"></label><label>Email<input type="email" name="email" value="<?= e(
    $d["email"],
) ?>"></label><div class="form-actions"><button class="btn">Save</button><a class="btn secondary" href="doctors.php">Cancel</a></div></form></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

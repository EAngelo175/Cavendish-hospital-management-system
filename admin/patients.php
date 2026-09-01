<?php $page_title = "Patient Directory";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$q = trim($_GET["q"] ?? "");
if ($q !== "") {
    $s = $conn->prepare(
        "SELECT * FROM patients WHERE patient_code LIKE ? OR name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY name",
    );
    $x = "%$q%";
    $s->execute([$x, $x, $x, $x]);
    $rows = $s->fetchAll();
} else {
    $rows = $conn
    ->query("SELECT * FROM patients ORDER BY id DESC")
        ->fetchAll();
}
?><div class="page-actions"><div><h2>Patients</h2><p class="muted">Directory for registered patients, including home-booking accounts.</p></div></div><form class="searchbar"><input name="q" value="<?= e(
    $q,
) ?>" placeholder="Search patient ID, name, phone or email"><button class="btn secondary">Search</button></form><div class="panel"><div class="table-wrap"><table><tr><th>Patient ID</th><th>Name</th><th>Gender</th><th>DOB</th><th>Phone</th><th>Email</th><th></th></tr><?php foreach (
    $rows
    as $p
): ?><tr><td><?= e($p["patient_code"]) ?></td><td><?= e($p["name"]) ?></td><td><?= e($p["gender"]) ?></td><td><?= e(
    $p["dob"],
) ?></td><td><?= e($p["phone"]) ?></td><td><?= e($p["email"]) ?></td><td><?= e(
    $p["blood_group"],
) ?></td><td><a class="mini-btn" href="patient_view.php?id=<?= $p[
    "id"
] ?>">View</a> <a class="mini-btn" href="../staff/patient_contact.php?id=<?= $p["id"] ?>">Contact</a></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

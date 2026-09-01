<?php
$page_title = "Patient Desk";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");
$query = trim($_GET["q"] ?? "");
$stmt = $conn->prepare("SELECT id, patient_code, name, gender, age, dob, phone, email, blood_group FROM patients WHERE patient_code LIKE ? OR name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY name");
$term = "%$query%";
$stmt->execute([$term, $term, $term, $term]);
$patients = $stmt->fetchAll();
?>
<div class="page-actions"><div><h2>Patient desk</h2><p class="muted">Find patient contact and registration details.</p></div></div>
<form class="searchbar"><input name="q" value="<?= e($query) ?>" placeholder="Search patients"><button class="btn secondary">Search</button></form>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient ID</th><th>Name</th><th>Age</th><th>Phone</th><th>Email</th><th></th></tr><?php foreach ($patients as $patient): ?><tr><td><?= e($patient["patient_code"]) ?></td><td><?= e($patient["name"]) ?></td><td><?= e($patient["age"]) ?></td><td><?= e($patient["phone"]) ?></td><td><?= e($patient["email"]) ?></td><td><a class="mini-btn" href="patient_edit.php?id=<?= $patient["id"] ?>">Edit</a><a class="mini-btn" href="patient_contact.php?id=<?= $patient["id"] ?>">Contact</a></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

<?php
$page_title = "Register Patient";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");
$error = "";
$patient_code = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $name = trim($_POST["name"] ?? "");
    $gender = $_POST["gender"] ?? "Male";
    $age = (int) ($_POST["age"] ?? 0);
    $email = trim($_POST["email"] ?? "");
    if ($name === "" || $age < 0 || $age > 130 || !in_array($gender, ["Male", "Female", "Other"], true)) {
        $error = "Enter a valid patient name, age and gender.";
    } elseif ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO patients (name, gender, age, dob, phone, address, blood_group, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $gender, $age, $_POST["dob"] ?: null, trim($_POST["phone"] ?? "") ?: null, trim($_POST["address"] ?? "") ?: null, trim($_POST["blood_group"] ?? "") ?: null, $email ?: null]);
        $patient_id = (int) $conn->lastInsertId();
        $patient_code = "PAT-" . str_pad((string) $patient_id, 6, "0", STR_PAD_LEFT);
        $conn->prepare("UPDATE patients SET patient_code = ? WHERE id = ?")->execute([$patient_code, $patient_id]);
        audit($conn, "Registered patient " . $patient_code, "patients");
    }
}
?>
<div class="page-actions"><div><h2>Register a patient</h2><p class="muted">Create a patient record and receive a patient ID.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($patient_code): ?><div class="alert success">Patient registered. Patient ID: <strong><?= e($patient_code) ?></strong></div><?php endif; ?>
<div class="panel"><form method="post" class="form-grid"><?= csrf_field() ?><label>Full name<input name="name" required></label><label>Gender<select name="gender"><option>Male</option><option>Female</option><option>Other</option></select></label><label>Age<input type="number" name="age" min="0" max="130" required></label><label>Date of birth<input type="date" name="dob"></label><label>Phone<input name="phone"></label><label>Email<input type="email" name="email"></label><label>Blood group<input name="blood_group"></label><label>Address<textarea name="address"></textarea></label><button class="btn">Register patient</button></form></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

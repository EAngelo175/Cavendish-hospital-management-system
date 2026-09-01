<?php
$page_title = "Edit Patient";
require_once __DIR__ . "/../Includes/header.php";
require_role("receptionist");

$patient_id = (int) ($_GET["id"] ?? 0);
$error = "";
$success = "";

if ($patient_id < 1) {
    $error = "Patient not found.";
} else {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
    
    if (!$patient) {
        $error = "Patient not found.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
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
        $stmt = $conn->prepare(
            "UPDATE patients 
             SET name = ?, gender = ?, age = ?, dob = ?, phone = ?, address = ?, blood_group = ?, email = ? 
             WHERE id = ?"
        );
        $stmt->execute([
            $name, 
            $gender, 
            $age, 
            $_POST["dob"] ?: null, 
            trim($_POST["phone"] ?? "") ?: null, 
            trim($_POST["address"] ?? "") ?: null, 
            trim($_POST["blood_group"] ?? "") ?: null, 
            $email ?: null, 
            $patient_id
        ]);
        $success = "Patient record updated successfully.";
        audit($conn, "Updated patient " . $patient["patient_code"], "patients");
        
        // Refresh patient data
        $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$patient_id]);
        $patient = $stmt->fetch();
    }
}
?>
<div class="page-actions">
    <div>
        <h2>Edit patient</h2>
        <p class="muted">Update patient information and contact details.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div>
    <div class="panel">
        <a class="btn" href="patients.php">Back to patients</a>
    </div>
<?php else: ?>
    <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
    
    <div class="panel">
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <label>Patient ID
                <input value="<?= e($patient["patient_code"]) ?>" disabled>
            </label>
            <label>Full name
                <input name="name" value="<?= e($patient["name"]) ?>" required>
            </label>
            <label>Gender
                <select name="gender">
                    <option <?= $patient["gender"] === "Male" ? "selected" : "" ?>>Male</option>
                    <option <?= $patient["gender"] === "Female" ? "selected" : "" ?>>Female</option>
                    <option <?= $patient["gender"] === "Other" ? "selected" : "" ?>>Other</option>
                </select>
            </label>
            <label>Age
                <input type="number" name="age" min="0" max="130" value="<?= e($patient["age"]) ?>" required>
            </label>
            <label>Date of birth
                <input type="date" name="dob" value="<?= e($patient["dob"]) ?>">
            </label>
            <label>Phone
                <input name="phone" value="<?= e($patient["phone"]) ?>">
            </label>
            <label>Email
                <input type="email" name="email" value="<?= e($patient["email"]) ?>">
            </label>
            <label>Blood group
                <input name="blood_group" value="<?= e($patient["blood_group"]) ?>">
            </label>
            <label>Address
                <textarea name="address"><?= e($patient["address"]) ?></textarea>
            </label>
            <button class="btn">Save changes</button>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

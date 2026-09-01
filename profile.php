<?php
$page_title = "My Profile";
require_once __DIR__ . "/config/app.php";
require_role(["admin", "doctor", "receptionist", "pharmacist", "lab", "accountant"]);
require_once __DIR__ . "/Includes/header.php";
$user_id = (int) $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    if (($_POST["action"] ?? "") === "photo") {
        $upload = $_FILES["profile_photo"] ?? null;
        $allowed_types = ["image/jpeg", "image/png", "image/webp"];
        if (!$upload || $upload["error"] !== UPLOAD_ERR_OK) {
            $error = "Choose a profile photo to upload.";
        } elseif ($upload["size"] > 5 * 1024 * 1024) {
            $error = "Profile photos must be smaller than 5 MB.";
        } else {
            $file_info = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $file_info->file($upload["tmp_name"]);
            if (!in_array($mime_type, $allowed_types, true)) {
                $error = "Only JPG, PNG and WebP images are allowed.";
            } else {
                $extensions = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
                $upload_dir = __DIR__ . "/assets/uploads/profiles";
                $file_name = bin2hex(random_bytes(16)) . "." . $extensions[$mime_type];
                if (!move_uploaded_file($upload["tmp_name"], $upload_dir . DIRECTORY_SEPARATOR . $file_name)) {
                    $error = "The profile photo could not be saved.";
                } else {
                    $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    $stmt->execute([$file_name, $user_id]);
                    if (!empty($user["profile_photo"])) {
                        $old_photo = $upload_dir . DIRECTORY_SEPARATOR . basename($user["profile_photo"]);
                        if (is_file($old_photo)) unlink($old_photo);
                    }
                    $user["profile_photo"] = $file_name;
                    $success = "Profile photo updated successfully.";
                }
            }
        }
    } else {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Enter a valid email address.";
        } elseif ($password !== "" && strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            // If password is being changed, send verification email instead of updating directly
            if ($password !== "") {
                $token = bin2hex(random_bytes(50));
                $expires_at = date("Y-m-d H:i:s", strtotime("+24 hours"));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET email = ?, pending_password = ?, password_reset_token = ?, password_reset_expires = ? WHERE id = ?");
                $stmt->execute([$email, $hashed_password, $token, $expires_at, $user_id]);
                
                $verify_link = BASE_URL . "/verify-password-change.php?token=" . urlencode($token);
                $verify_message = "Dear " . e($user["username"]) . ",\n\n"
                    . "A password change request has been made for your account at " . HOSPITAL_NAME . ".\n\n"
                    . "To confirm this password change, click the link below:\n"
                    . $verify_link . "\n\n"
                    . "This link will expire in 24 hours.\n\n"
                    . "If you did not request this change, please ignore this email and your password will not be changed.\n\n"
                    . "Thank you,\n"
                    . HOSPITAL_NAME . " Team";
                
                send_patient_verification_email($email, "Verify Your Password Change - " . HOSPITAL_NAME, $verify_message);
                $success = "Verification email sent to " . e($email) . ". Please check your email to confirm the password change.";
                $user["email"] = $email;
            } else {
                // Only email is being changed
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$email, $user_id]);
                $success = "Email updated successfully.";
                $user["email"] = $email;
            }
        }
    }
}
?>
<div class="page-actions"><div><h2>My profile</h2><p class="muted">Update your photo, email or password.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<div class="grid two">
    <div class="panel profile-photo-panel">
        <h2>Profile photo</h2>
        <?php if (!empty($user["profile_photo"])): ?><img class="profile-photo" src="<?= BASE_URL ?>/assets/uploads/profiles/<?= e($user["profile_photo"]) ?>" alt="Profile photo">
        <?php else: ?><div class="profile-photo profile-photo-placeholder"><?= e(strtoupper(substr($user["username"], 0, 1))) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="photo">
            <label>Choose photo<input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required></label>
            <button class="btn">Change profile photo</button>
        </form>
    </div>
    <div class="panel">
        <h2>Account details</h2>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="details">
            <label>Username<input value="<?= e($user["username"]) ?>" disabled></label>
            <label>Role<input value="<?= e(ucfirst($user["role"])) ?>" disabled></label>
            <label>Email<input type="email" name="email" value="<?= e($user["email"]) ?>" required></label>
            <label>New password<input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password"></label>
            <button class="btn">Save account details</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . "/Includes/footer.php"; ?>

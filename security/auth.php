<?php
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["doctor_id"] = $user["doctor_id"] ?? null;
    $_SESSION["patient_id"] = $user["patient_id"] ?? null;
    $_SESSION["last_activity"] = time();
}
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $p["path"],
            $p["domain"],
            $p["secure"],
            $p["httponly"],
        );
    }
    session_destroy();
}
function current_user_id(): ?int
{
    return isset($_SESSION["user_id"]) ? (int) $_SESSION["user_id"] : null;
}

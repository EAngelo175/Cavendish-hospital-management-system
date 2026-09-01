<?php
function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrf_token()) .
        '">';
}
function verify_csrf(): void
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = $_POST["csrf_token"] ?? "";
        if (!$token || !hash_equals($_SESSION["csrf_token"] ?? "", $token)) {
            http_response_code(403);
            exit("Invalid CSRF token.");
        }
    }
}

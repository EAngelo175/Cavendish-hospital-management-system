<?php
function require_role(array|string $roles): void
{
    $roles = (array) $roles;
    if (
        empty($_SESSION["user_id"]) ||
        !in_array($_SESSION["role"] ?? "", $roles, true)
    ) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

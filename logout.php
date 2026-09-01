<?php require_once __DIR__ . "/config/app.php";
logout_user();
header("Location: " . BASE_URL . "/index.php");
exit(); ?>

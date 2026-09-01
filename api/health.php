<?php require_once __DIR__ . "/bootstrap.php";
json_response([
    "success" => true,
    "service" => HOSPITAL_NAME . " API",
    "time" => date("c"),
]); ?>

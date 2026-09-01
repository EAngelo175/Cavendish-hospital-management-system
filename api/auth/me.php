<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role([
    "admin",
    "doctor",
    "patient",
    "accountant",
    "receptionist",
    "pharmacist",
    "lab",
    "nurse",
]);
json_response([
    "success" => true,
    "user" => [
        "id" => (int) $u["id"],
        "username" => $u["username"],
        "email" => $u["email"],
        "role" => $u["role"],
        "patient_id" => $u["patient_id"] ?? null,
        "doctor_id" => $u["doctor_id"] ?? null,
    ],
]); ?>

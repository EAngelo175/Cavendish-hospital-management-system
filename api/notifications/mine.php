<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role([
    "patient",
    "doctor",
    "admin",
    "accountant",
    "receptionist",
    "pharmacist",
    "lab",
    "nurse",
]);
$s = $conn->prepare(
    "SELECT id,title,message,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50",
);
$s->execute([$u["id"]]);
json_response(["success" => true, "notifications" => $s->fetchAll()]); ?>

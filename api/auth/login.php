<?php require_once __DIR__ . "/../bootstrap.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "POST required"], 405);
}
$d = api_input();
$login = trim($d["login"] ?? "");
$password = $d["password"] ?? "";
$s = $conn->prepare(
    "SELECT u.*,p.id patient_id FROM users u LEFT JOIN patients p ON p.user_id=u.id WHERE u.username=? OR u.email=? LIMIT 1",
);
$s->execute([$login, $login]);
$u = $s->fetch();
if (!$u || !password_verify($password, $u["password"])) {
    json_response(
        ["success" => false, "message" => "Invalid credentials"],
        401,
    );
}
$token = api_issue_token((int) $u["id"]);
json_response([
    "success" => true,
    "token" => $token,
    "expires_in" => 604800,
    "user" => [
        "id" => (int) $u["id"],
        "username" => $u["username"],
        "role" => $u["role"],
        "patient_id" => $u["patient_id"],
    ],
]); ?>

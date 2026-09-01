<?php
declare(strict_types=1);
require_once __DIR__ . "/../config/app.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit();
}
function api_input(): array
{
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}
function api_user(): ?array
{
    $header = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
    if (!preg_match("/Bearer\s+(.+)/i", $header, $m)) {
        return null;
    }
    $hash = hash("sha256", trim($m[1]));
    $s = $GLOBALS["conn"]->prepare(
        "SELECT u.*,p.id patient_id FROM api_tokens t JOIN users u ON u.id=t.user_id LEFT JOIN patients p ON p.user_id=u.id WHERE t.token_hash=? AND t.expires_at>NOW() LIMIT 1",
    );
    $s->execute([$hash]);
    return $s->fetch() ?: null;
}
function api_require_role(array|string $roles): array
{
    $u = api_user();
    if (!$u) {
        json_response(
            ["success" => false, "message" => "Authentication required"],
            401,
        );
    }
    if (!in_array($u["role"], (array) $roles, true)) {
        json_response(["success" => false, "message" => "Forbidden"], 403);
    }
    return $u;
}
function api_issue_token(int $userId): string
{
    $raw = bin2hex(random_bytes(32));
    $hash = hash("sha256", $raw);
    $s = $GLOBALS["conn"]->prepare(
        "INSERT INTO api_tokens(user_id,token_hash,expires_at,created_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 7 DAY),NOW())",
    );
    $s->execute([$userId, $hash]);
    return $raw;
}

<?php require_once __DIR__ . "/../bootstrap.php";
$u = api_require_role(["patient", "admin", "receptionist"]);
$rows = $conn
    ->query(
        "SELECT id,name,specialization,phone,email FROM doctors ORDER BY name",
    )
    ->fetchAll();
json_response(["success" => true, "doctors" => $rows]); ?>

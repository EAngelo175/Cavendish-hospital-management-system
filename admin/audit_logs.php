<?php $page_title = "Audit Logs";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$rows = $conn
    ->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 200")
    ->fetchAll();
?><div class="page-actions"><div><h2>Audit logs</h2><p class="muted">Review administrative activity.</p></div></div><div class="panel"><div class="table-wrap"><table><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>IP</th></tr><?php foreach (
    $rows
    as $r
): ?><tr><td><?= e($r["created_at"]) ?></td><td><?= e(
    $r["username"],
) ?></td><td><?= e($r["action"]) ?></td><td><?= e(
    $r["module"],
) ?></td><td><?= e(
    $r["ip_address"],
) ?></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

<?php
$page_title = "User Management";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$q = trim($_GET["q"] ?? "");
$role = $_GET["role"] ?? "";
$sql =
    "SELECT u.id,u.username,u.email,u.role,u.created_at FROM users u WHERE 1=1";
$p = [];
if ($q !== "") {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $p = ["%$q%", "%$q%"];
}
$valid = [
    "admin",
    "doctor",
    "receptionist",
    "pharmacist",
    "lab",
    "accountant",
    "patient",
];
if (in_array($role, $valid, true)) {
    $sql .= " AND u.role=?";
    $p[] = $role;
}
$sql .= " ORDER BY u.id DESC";
$s = $conn->prepare($sql);
$s->execute($p);
$users = $s->fetchAll();
?><div class="page-actions"><div><h2>Users</h2><p class="muted">Create and manage portal accounts.</p></div><a class="btn" href="user_create.php">+ Create user</a></div><form class="searchbar"><input name="q" value="<?= e(
    $q,
) ?>" placeholder="Search username or email"><select name="role"><option value="">All roles</option><?php foreach (
    $valid
    as $r
): ?><option value="<?= $r ?>" <?= $role === $r
    ? "selected"
    : "" ?>><?= $r ?></option><?php endforeach; ?></select><button class="btn secondary">Filter</button></form><div class="panel"><div class="table-wrap"><table><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th></th></tr><?php foreach (
    $users
    as $u
): ?><tr><td><?= $u["id"] ?></td><td><?= e($u["username"]) ?></td><td><?= e(
    $u["email"],
) ?></td><td><?= e($u["role"]) ?></td><td><?= e(
    $u["created_at"],
) ?></td><td><a class="mini-btn" href="user_edit.php?id=<?= $u[
    "id"
] ?>">Edit</a></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php";

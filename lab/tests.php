<?php
$page_title = "Laboratory Tests";
require_once __DIR__ . "/../Includes/header.php";
require_role("lab");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();
    $status = $_POST["status"] ?? "Pending";
    if (in_array($status, ["Pending", "Processing", "Completed"], true)) {
        $stmt = $conn->prepare("UPDATE lab_tests SET status = ?, result = ? WHERE id = ?");
        $stmt->execute([$status, trim($_POST["result"] ?? ""), (int) $_POST["id"]]);
    }
    header("Location: tests.php");
    exit();
}
$tests = $conn->query("SELECT l.*, p.name patient_name FROM lab_tests l LEFT JOIN patients p ON p.id = l.patient_id ORDER BY l.id DESC")->fetchAll();
?>
<div class="page-actions"><div><h2>Laboratory tests</h2><p class="muted">Process requests and record test results.</p></div></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Patient</th><th>Test</th><th>Status</th><th>Result</th><th>Update</th></tr><?php foreach ($tests as $test): ?><tr><td><?= e($test["patient_name"]) ?></td><td><?= e($test["test_name"]) ?></td><td><?= e($test["status"]) ?></td><td><?= e($test["result"]) ?></td><td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $test["id"] ?>"><select name="status"><option>Pending</option><option>Processing</option><option>Completed</option></select><input name="result" placeholder="Result"><button class="mini-btn">Save</button></form></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

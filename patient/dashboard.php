<?php $page_title = "Patient Dashboard";
require_once __DIR__ . "/../Includes/header.php";
require_role("patient");
$pid = (int) $_SESSION["patient_id"];
$s = $conn->prepare(
    "SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status IN ('Pending','Approved')",
);
$s->execute([$pid]);
$upcoming = (int) $s->fetchColumn();
$s = $conn->prepare(
    "SELECT a.*,d.name doctor_name FROM appointments a JOIN doctors d ON d.id=a.doctor_id WHERE a.patient_id=? ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 5",
);
$s->execute([$pid]);
$rows = $s->fetchAll();
$s = $conn->prepare(
    "SELECT i.id, i.invoice_number, i.total_amount, i.status, i.created_at,
        COALESCE(SUM(bp.amount), 0) paid_amount,
        (SELECT GROUP_CONCAT(ii.description SEPARATOR ', ')
         FROM invoice_items ii WHERE ii.invoice_id = i.id) item_list
     FROM invoices i
     LEFT JOIN billing_payments bp ON bp.invoice_id = i.id
     WHERE i.patient_id = ?
     GROUP BY i.id, i.invoice_number, i.total_amount, i.status, i.created_at
     ORDER BY i.created_at DESC LIMIT 5",
);
$s->execute([$pid]);
$bills = $s->fetchAll();
?><div class="page-actions"><div><h2>Welcome back</h2><p class="muted">Manage your care from home.</p></div><a class="btn" href="book.php">+ Book appointment</a></div><div class="cards"><div class="card"><span>Upcoming appointments</span><strong><?= $upcoming ?></strong></div></div><div class="panel"><div class="panel-head"><h2>My appointments</h2><a href="appointments.php">View all</a></div><div class="table-wrap"><table><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr><?php foreach (
    $rows
    as $r
): ?><tr><td><?= e($r["doctor_name"]) ?></td><td><?= e(
    $r["appointment_date"],
) ?></td><td><?= e($r["appointment_time"]) ?></td><td><?= e(
    $r["status"],
) ?></td></tr><?php endforeach; ?></table></div></div><div class="panel"><div class="panel-head"><h2>My bills</h2><a href="bills.php">View all</a></div><div class="table-wrap"><table><tr><th>Item</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr><?php foreach ($bills as $bill): ?><tr><td><?= e($bill["item_list"] ?: "Invoice") ?></td><td>UGX <?= number_format((float) $bill["total_amount"]) ?></td><td>UGX <?= number_format((float) $bill["paid_amount"]) ?></td><td>UGX <?= number_format((float) $bill["total_amount"] - (float) $bill["paid_amount"]) ?></td><td><?= e($bill["status"]) ?></td><td><?php if ((float) $bill["total_amount"] > (float) $bill["paid_amount"]): ?><a class="mini-btn" href="pay.php?invoice_id=<?= $bill["id"] ?>">Pay</a><?php endif; ?></td></tr><?php endforeach; ?></table></div></div><?php require_once __DIR__ .
    "/../Includes/footer.php"; ?>

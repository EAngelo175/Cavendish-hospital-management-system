<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin"]);
$stats = [
    "patients" => $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    "doctors" => $conn->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
    "users" => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    "today" => $conn
        ->query(
            "SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE()",
        )
        ->fetchColumn(),
    "pending" => $conn
        ->query("SELECT COUNT(*) FROM appointments WHERE status='Pending'")
        ->fetchColumn(),
    "admitted" => $conn
        ->query("SELECT COUNT(*) FROM admissions WHERE status='Admitted'")
        ->fetchColumn(),
    "beds" => $conn
        ->query("SELECT COUNT(*) FROM beds WHERE status='Available'")
        ->fetchColumn(),
    "revenue" => $conn
        ->query(
            "SELECT COALESCE(SUM(amount),0) FROM billing_payments WHERE DATE(payment_date)=CURDATE()",
        )
        ->fetchColumn(),
];
$recent = $conn
    ->query(
        "SELECT a.*,p.name patient_name,d.name doctor_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 8",
    )
    ->fetchAll();
?>
<div class="hero"><div class="kicker">System administration</div><h2>Hospital operations at a glance</h2><p>Manage users, clinical staff, patients, appointments and resources.</p></div>
<div class="cards">
<?php foreach (
    [
        ["Patients", $stats["patients"]],
        ["Doctors", $stats["doctors"]],
        ["System users", $stats["users"]],
        ["Today appointments", $stats["today"]],
        ["Pending appointments", $stats["pending"]],
        ["Admitted patients", $stats["admitted"]],
        ["Available beds", $stats["beds"]],
        ["Today revenue", "UGX " . number_format($stats["revenue"])],
    ]
    as $c
): ?><div class="card"><h3><?= e($c[0]) ?></h3><strong><?= e(
    (string) $c[1],
) ?></strong></div><?php endforeach; ?>
</div>
<div class="panel"><h2>Quick actions</h2><div class="actions"><a class="btn" href="user_create.php">+ Create user</a><a class="btn" href="doctor_create.php">+ Add doctor</a><a class="btn" href="appointments.php">Appointments</a><a class="btn secondary" href="patients.php">Patients</a><a class="btn secondary" href="beds.php">Beds</a><a class="btn secondary" href="reports.php">Reports</a></div></div>
<div class="panel"><div class="panel-head"><h2>Recent appointments</h2><a href="appointments.php">View all</a></div><div class="table-wrap"><table><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr><?php foreach (
    $recent
    as $r
): ?><tr><td><?= e($r["patient_name"]) ?></td><td><?= e(
    $r["doctor_name"],
) ?></td><td><?= e(
    $r["appointment_date"] . " " . $r["appointment_time"],
) ?></td><td><?= e(
    $r["status"],
) ?></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

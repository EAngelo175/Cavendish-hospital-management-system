<?php
$page_title = "Contact Patient";
require_once __DIR__ . "/../Includes/header.php";
require_role(["admin", "receptionist"]);
$id = (int) ($_GET["id"] ?? 0);
$stmt = $conn->prepare("SELECT id, patient_code, name, email, phone FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();
if (!$patient) exit("Patient not found.");
?>
<div class="page-actions"><div><h2>Contact patient</h2><p class="muted"><?= e($patient["patient_code"] . " · " . $patient["name"]) ?></p></div></div>
<div class="panel"><h2><?= e($patient["name"]) ?></h2><p>Email: <?= e($patient["email"] ?: "Not provided") ?></p><p>Phone: <?= e($patient["phone"] ?: "Not provided") ?></p><div class="actions"><?php if ($patient["email"]): ?><a class="btn" href="mailto:<?= e($patient["email"]) ?>?subject=<?= rawurlencode(HOSPITAL_NAME . " follow-up") ?>">Email patient</a><?php endif; ?><?php if ($patient["phone"]): ?><a class="btn secondary" href="tel:<?= e($patient["phone"]) ?>">Call patient</a><a class="btn secondary" href="sms:<?= e($patient["phone"]) ?>">Send SMS</a><?php endif; ?></div></div>

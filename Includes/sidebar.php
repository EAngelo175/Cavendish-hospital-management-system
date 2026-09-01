<?php $role = $_SESSION["role"] ?? ""; ?>
<aside class="sidebar">
	<div class="brand" style="display:flex;align-items:center;gap:10px;">
		<img src="<?= e(HOSPITAL_LOGO) ?>" alt="<?= e(HOSPITAL_NAME) ?> logo" width="30" height="30">
		<span><?= e(HOSPITAL_NAME) ?></span>
	</div>
	<nav>
		<a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
		<?php if ($role !== "patient"): ?><a href="<?= BASE_URL ?>/profile.php">My profile</a><?php endif; ?>
		<a href="<?= BASE_URL ?>/notifications.php">Notifications</a>
		<?php if ($role !== "patient"): ?><a href="<?= BASE_URL ?>/messages.php">Messages</a><?php endif; ?>
		<?php if ($role === "admin"): ?>
			<a href="<?= BASE_URL ?>/admin/users.php">Users</a>
			<a href="<?= BASE_URL ?>/admin/doctors.php">Doctors</a>
			<a href="<?= BASE_URL ?>/admin/doctor_schedule.php">Doctor schedules</a>
			<a href="<?= BASE_URL ?>/admin/patients.php">Patients</a>
			<a href="<?= BASE_URL ?>/admin/appointments.php">Appointments</a>
			<a href="<?= BASE_URL ?>/admin/beds.php">Beds</a>
			<a href="<?= BASE_URL ?>/admin/reports.php">Reports</a>
			<a href="<?= BASE_URL ?>/admin/audit_logs.php">Audit logs</a>
		<?php elseif ($role === "doctor"): ?>
			<a href="<?= BASE_URL ?>/doctor/appointments.php">Appointments</a>
			<a href="<?= BASE_URL ?>/doctor/patients.php">Patients</a>
			<a href="<?= BASE_URL ?>/doctor/records.php">Medical records</a>
			<a href="<?= BASE_URL ?>/doctor/prescriptions.php">Prescriptions</a>
			<a href="<?= BASE_URL ?>/doctor/lab_requests.php">Lab requests</a>
		<?php elseif ($role === "patient"): ?>
			<a href="<?= BASE_URL ?>/patient/book.php">Book appointment</a>
			<a href="<?= BASE_URL ?>/patient/appointments.php">My appointments</a>
			<a href="<?= BASE_URL ?>/patient/records.php">Medical records</a>
			<a href="<?= BASE_URL ?>/patient/bills.php">Bills</a>
		<?php elseif ($role === "receptionist"): ?>
			<a href="<?= BASE_URL ?>/staff/patient_create.php">Register patient</a>
			<a href="<?= BASE_URL ?>/staff/appointments.php">Appointments</a>
			<a href="<?= BASE_URL ?>/accounting/invoices.php">Invoices</a>
			<a href="<?= BASE_URL ?>/staff/patients.php">Patients</a>
			<a href="<?= BASE_URL ?>/staff/admissions.php">Admissions</a>
			<a href="<?= BASE_URL ?>/staff/patient_contact.php">Contact patients</a>
		<?php elseif ($role === "pharmacist"): ?>
			<a href="<?= BASE_URL ?>/pharmacy/prescriptions.php">Prescriptions</a>
			<a href="<?= BASE_URL ?>/pharmacy/medicines.php">Medicine inventory</a>
		<?php elseif ($role === "lab"): ?>
			<a href="<?= BASE_URL ?>/lab/tests.php">Laboratory tests</a>
		<?php elseif ($role === "accountant"): ?>
			<a href="<?= BASE_URL ?>/accounting/invoices.php">Invoices</a>
			<a href="<?= BASE_URL ?>/accounting/payments.php">Payments</a>
			<a href="<?= BASE_URL ?>/accounting/claims.php">Insurance claims</a>
			<a href="<?= BASE_URL ?>/accounting/reports.php">Finance reports</a>
		<?php endif; ?>
		<a href="<?= BASE_URL ?>/logout.php">Logout</a>
	</nav>
</aside>

<?php
$page_title = "Medicine Inventory";
require_once __DIR__ . "/../Includes/header.php";
require_role("pharmacist");
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	verify_csrf();
	$action = $_POST["action"] ?? "add";
	
	if ($action === "add") {
		$name = trim($_POST["name"] ?? "");
		$quantity = (int) ($_POST["quantity"] ?? 0);
		$price = (float) ($_POST["price"] ?? 0);
		if ($name === "" || $quantity < 0 || $price < 0) {
			$error = "Enter valid medicine details.";
		} else {
			$stmt = $conn->prepare("INSERT INTO medicines (name, category, quantity, price, expiry_date) VALUES (?, ?, ?, ?, ?)");
			$stmt->execute([$name, trim($_POST["category"] ?? "") ?: null, $quantity, $price, $_POST["expiry_date"] ?: null]);
			$success = "Medicine added successfully.";
		}
	} elseif ($action === "edit") {
		$medicine_id = (int) $_POST["medicine_id"];
		$name = trim($_POST["name"] ?? "");
		$quantity = (int) ($_POST["quantity"] ?? 0);
		$price = (float) ($_POST["price"] ?? 0);
		if ($medicine_id < 1 || $name === "" || $quantity < 0 || $price < 0) {
			$error = "Enter valid medicine details.";
		} else {
			$stmt = $conn->prepare("UPDATE medicines SET name = ?, category = ?, quantity = ?, price = ?, expiry_date = ? WHERE id = ?");
			$stmt->execute([$name, trim($_POST["category"] ?? "") ?: null, $quantity, $price, $_POST["expiry_date"] ?: null, $medicine_id]);
			$success = "Medicine updated successfully.";
		}
	} elseif ($action === "delete") {
		$medicine_id = (int) $_POST["medicine_id"];
		if ($medicine_id > 0) {
			$stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
			$stmt->execute([$medicine_id]);
			$success = "Medicine deleted successfully.";
		}
	}
}

$medicines = $conn->query("SELECT * FROM medicines ORDER BY name")->fetchAll();
$edit_id = (int) ($_GET["edit"] ?? 0);
$edit_medicine = null;
if ($edit_id > 0) {
	$stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
	$stmt->execute([$edit_id]);
	$edit_medicine = $stmt->fetch();
}
?>
<div class="page-actions"><div><h2>Medicine inventory</h2><p class="muted">Monitor available stock and expiry dates.</p></div></div>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
<div class="panel"><h2><?= $edit_medicine ? "Edit medicine" : "Add medicine" ?></h2><form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit_medicine ? "edit" : "add" ?>"><input type="hidden" name="medicine_id" value="<?= $edit_medicine["id"] ?? "" ?>"><label>Name<input name="name" value="<?= $edit_medicine ? e($edit_medicine["name"]) : "" ?>" required></label><label>Category<input name="category" value="<?= $edit_medicine ? e($edit_medicine["category"]) : "" ?>"></label><label>Quantity<input type="number" name="quantity" value="<?= $edit_medicine ? e($edit_medicine["quantity"]) : 0 ?>" min="0" required></label><label>Price<input type="number" name="price" value="<?= $edit_medicine ? e($edit_medicine["price"]) : 0 ?>" min="0" step="0.01" required></label><label>Expiry<input type="date" name="expiry_date" value="<?= $edit_medicine ? e($edit_medicine["expiry_date"]) : "" ?>"></label><button class="btn"><?= $edit_medicine ? "Update medicine" : "Add medicine" ?></button></form><?php if ($edit_medicine): ?><a class="mini-btn" href="medicines.php" style="margin-top:8px;">Cancel</a><?php endif; ?></div>
<div class="panel"><div class="table-wrap"><table><tr><th>Medicine</th><th>Category</th><th>Quantity</th><th>Price</th><th>Expiry</th><th></th></tr><?php foreach ($medicines as $medicine): ?><tr><td><?= e($medicine["name"]) ?></td><td><?= e($medicine["category"] ?: "—") ?></td><td><?= e($medicine["quantity"]) ?></td><td>UGX <?= number_format((float) $medicine["price"]) ?></td><td><?= e($medicine["expiry_date"] ?: "—") ?></td><td><a class="mini-btn" href="?edit=<?= $medicine["id"] ?>">Edit</a><form method="post" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="medicine_id" value="<?= $medicine["id"] ?>"><button class="mini-btn" onclick="return confirm('Delete this medicine?');">Delete</button></form></td></tr><?php endforeach; ?></table></div></div>
<?php require_once __DIR__ . "/../Includes/footer.php"; ?>

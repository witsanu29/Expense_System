<?php

session_start();
require '../includes/config.php';
require '../includes/auth.php';

/* ===== กัน Demo ===== */
if ($_SESSION['role'] === 'demo') {
    $_SESSION['popup_error'] = '⛔ บัญชี Demo สามารถดูข้อมูลได้อย่างเดียว';
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];


/* ===== ดึงหมวดของ user ===== */
$stmt = $conn->prepare("
    SELECT id, type, name
    FROM categories
    ORDER BY type, name
");
$stmt->execute();
$catResult = $stmt->get_result();

$categories = [];
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}


/* ===== ดึง transaction_typer ===== */
$typeResult = $conn->query("
    SELECT code, name
    FROM transaction_types
    ORDER BY id
");

/* แปลงเป็น array (สำคัญมาก) */
$types = [];
while ($row = $typeResult->fetch_assoc()) {
    $types[] = $row;
}

/* ===== บันทึกข้อมูล ===== */
if (isset($_POST['save'])) {

    $category_id = (int)($_POST['category_id'] ?? 0);
    $trans_date  = $_POST['trans_date'] ?? '';
    $description = $_POST['description'] ?? '';
    $amount      = floatval($_POST['amount'] ?? 0);

    if ($category_id <= 0) {
        die('❌ กรุณาเลือกหมวด');
    }

    if ($trans_date === '' || $amount <= 0) {
        die('❌ ข้อมูลไม่ครบ');
    }

    /* 🔍 ตรวจสอบหมวด + type */
	$stmt = $conn->prepare("
		SELECT type, name
		FROM categories
		WHERE id = ?
	");
	$stmt->bind_param("i", $category_id);
	$stmt->execute();
	$cat = $stmt->get_result()->fetch_assoc();

	if (!$cat) {
		die('❌ หมวดไม่ถูกต้อง');
	}

	$type     = $cat['type'];
	$category = $cat['name'];


    /* ===== INSERT ===== */
    $stmt = $conn->prepare("
        INSERT INTO transactions
        (user_id, trans_date, type, category, description, amount)
        VALUES (?,?,?,?,?,?)
    ");
    $stmt->bind_param(
        "issssd",
        $user_id,
        $trans_date,
        $type,
        $category,
        $description,
        $amount
    );
    $stmt->execute();

    $_SESSION['toast_success'] = true;
    header('Location: ../index.php');
    exit;
}
?>




<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เพิ่มรายรับรายจ่าย</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
	body {
		background: #f5f7fb;
	}

	.card-header-gradient {
		background: linear-gradient(135deg, #0d6efd, #0b5ed7);
	}

	.form-label {
		margin-bottom: .25rem;
	}

	.amount-input {
		font-size: 1.25rem;
		font-weight: 600;
	}

	.badge-type {
		font-size: .9rem;
	}

</style>
<style>
.card-income {
	background: linear-gradient(135deg, #198754, #157347);
}
.card-expense {
	background: linear-gradient(135deg, #dc3545, #b02a37);
}
</style>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar.php'; ?>

		<div class="toast-container position-fixed bottom-0 end-0 p-3">

			<div id="successToast"
				 class="toast align-items-center text-bg-success border-0"
				 role="alert">
				<div class="d-flex">
					<div class="toast-body">
						<i class="bi bi-check-circle me-1"></i>
						บันทึกรายการเรียบร้อยแล้ว
					</div>
					<button type="button" class="btn-close btn-close-white me-2 m-auto"
							data-bs-dismiss="toast"></button>
				</div>
			</div>

		</div>

      <div class="container-fluid px-4 py-4">
		<div id="cardHeader" class="card-header text-white">

			<div class="card shadow-lg border-0">

				<div class="card-header card-header-gradient text-white">
					<div class="d-flex align-items-center justify-content-between">
						<div class="fs-5">
							<i class="bi bi-wallet2 me-2"></i> เพิ่มรายการรายรับ–รายจ่าย
						</div>
						<span id="typeBadge" class="badge bg-light text-dark badge-type d-none"></span>
					</div>
				</div>

				<div class="card-body p-4">

				<form method="post">
				<div class="row g-3 align-items-end">

					<!-- ===== ประเภทรายการ ===== -->
					<div class="col-md-4">
						<label class="form-label fw-semibold">ประเภทรายการ</label>
						<select name="type" id="typeSelect" class="form-select" required>
							<option value="">-- เลือกประเภท --</option>
							<?php foreach ($types as $t): ?>
								<option value="<?= $t['code'] ?>">
									<?= $t['code']=='income'?'💰':'💸' ?>
									<?= htmlspecialchars($t['name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- ===== หมวดหมู่ ===== -->
					<div class="col-md-5">
						<label class="form-label fw-semibold">หมวดหมู่</label>
						<select name="category_id" id="categorySelect"
								class="form-select" required disabled>
							<option value="">-- เลือกหมวด --</option>
							<?php foreach ($categories as $c): ?>
								<option value="<?= $c['id'] ?>"
										data-type="<?= $c['type'] ?>"
										hidden>
									<?= $c['type']=='income'?'💰':'💸' ?>
									<?= htmlspecialchars($c['name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- ===== วันที่ ===== -->
					<div class="col-md-3">
						<label class="form-label fw-semibold">วันที่</label>
						<input type="date" name="trans_date"
							   class="form-control" required>
					</div>

				</div>

						<hr class="my-4">

						<div class="mb-3">
							<label class="form-label fw-semibold">
								<i class="bi bi-card-text me-1"></i> รายละเอียด
							</label>
							<input type="text" name="description" class="form-control"
								   placeholder="เช่น ค่าอาหารกลางวัน">
						</div>

						<div class="mb-4">
							<label class="form-label fw-semibold">
								<i class="bi bi-cash-coin me-1"></i> จำนวนเงิน
							</label>
							<div class="input-group input-group-lg">
								<span class="input-group-text bg-light">฿</span>
								<input type="number" step="0.01" name="amount"
									   class="form-control text-end amount-input"
									   placeholder="0.00" required>
							</div>
						</div>

						<div class="d-flex justify-content-between pt-3 border-top">
							<a href="../index.php" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-left"></i> กลับ
							</a>

							<?php if ($_SESSION['role'] !== 'demo'): ?>
							<button name="save" class="btn btn-success btn-lg px-4">
								<i class="bi bi-save me-1"></i> บันทึกข้อมูล
							</button>
							<?php else: ?>
							<button class="btn btn-secondary btn-lg px-4" disabled>
								<i class="bi bi-eye"></i> โหมดดูอย่างเดียว
							</button>
							<?php endif; ?>

						</div>

					</form>

				</div>
			</div>
		</div>
		</div>

		<script>
		const typeSelect     = document.getElementById('typeSelect');
		const categorySelect = document.getElementById('categorySelect');

		typeSelect.addEventListener('change', function () {

			const selectedType = this.value;

			categorySelect.value = '';
			categorySelect.disabled = selectedType === '';

			let found = false;

			Array.from(categorySelect.options).forEach(opt => {
				if (!opt.value) return;

				if (opt.dataset.type === selectedType) {
					opt.hidden = false;
					found = true;
				} else {
					opt.hidden = true;
				}
			});

			if (!found && selectedType !== '') {
				alert('⚠️ ยังไม่มีหมวดในประเภทรายการนี้');
			}
		});
		</script>

<?php include '../vendor/footer.php'; ?>
</body>
</html>

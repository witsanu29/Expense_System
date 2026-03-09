<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';

$user_id = $_SESSION['user_id'] ?? 0;
$role    = $_SESSION['role'] ?? 'demo';

if (!in_array($role, ['admin','user'])) {
    die('ไม่มีสิทธิ์เข้าถึง');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ไม่พบรายการ');


/* ===== ดึงข้อมูลรายการเดิม ===== */
$stmt = $conn->prepare("
    SELECT *
    FROM transactions
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die('ไม่พบข้อมูล');

if ($role === 'user' && $data['user_id'] != $user_id) {
    die('ไม่มีสิทธิ์แก้ไขรายการนี้');
}


/* ===== บันทึก ===== */
$error = '';

if (isset($_POST['save'])) {

    $trans_date  = $_POST['trans_date'] ?? '';
    $type        = $_POST['type'] ?? '';
    $category    = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount      = (float)($_POST['amount'] ?? 0);

    if (
        $trans_date === '' ||
        !in_array($type, ['income','expense']) ||
        $category === '' ||
        $amount <= 0
    ) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {

        $stmt = $conn->prepare("
            UPDATE transactions
            SET
                trans_date  = ?,
                type        = ?,
                category    = ?,
                description = ?,
                amount      = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssssdi",
            $trans_date,
            $type,
            $category,
            $description,
            $amount,
            $id
        );
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}
?>



<head>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->

	<div class="container-fluid px-4 py-4">
		<div class="card shadow-sm">
			<div class="card-header bg-warning">
			<i class="bi bi-pencil-square"></i> แก้ไขรายการ
			</div>

				<div class="card-body">

				<?php if ($error): ?>
				<div class="alert alert-danger"><?= $error ?></div>
				<?php endif; ?>

					<form method="post">

						<div class="mb-3">
							<label class="form-label">วันที่</label>
							<input type="date" name="trans_date" class="form-control"
								   value="<?= $data['trans_date'] ?>" required>
						</div>

						<div class="mb-3">
							<label class="form-label">ประเภท</label>
							<select name="type" id="typeSelect" class="form-select" required>
								<option value="income"  <?= $data['type']=='income'?'selected':'' ?>>รายรับ</option>
								<option value="expense" <?= $data['type']=='expense'?'selected':'' ?>>รายจ่าย</option>
							</select>
						</div>

						<div class="mb-3">
							<label class="form-label">หมวดหมู่</label>
							<select name="category" id="categorySelect" class="form-select" required>
								<!-- JS จะเติม option -->
							</select>
						</div>


						<div class="mb-3">
							<label class="form-label">รายละเอียด</label>
							<input type="text" name="description" class="form-control"
								   value="<?= htmlspecialchars($data['description']) ?>">
						</div>

						<div class="mb-3">
							<label class="form-label">จำนวนเงิน</label>
							<input type="number" step="0.01" name="amount" class="form-control"
								   value="<?= $data['amount'] ?>" required>
						</div>

						<button name="save" class="btn btn-success">
							<i class="bi bi-save"></i> บันทึก
						</button>
						<a href="../index.php" class="btn btn-secondary">ยกเลิก</a>

					</form>
				</div>
		</div>
	</div>

	<script>
	const typeSelect = document.getElementById('typeSelect');
	const catSelect  = document.getElementById('categorySelect');
	const currentCat = "<?= addslashes($data['category']) ?>";

	function loadCategories(type) {
		catSelect.innerHTML = '<option value="">-- เลือกหมวด --</option>';

		fetch('ajax_get_categories.php?type=' + type)
			.then(res => res.json())
			.then(list => {
				list.forEach(cat => {
					const opt = document.createElement('option');
					opt.value = cat;
					opt.textContent = cat;

					// ⭐ เลือกค่าเดิมอัตโนมัติ
					if (cat === currentCat) {
						opt.selected = true;
					}

					catSelect.appendChild(opt);
				});
			});
	}

	// โหลดครั้งแรก (ตอนเข้า edit)
	loadCategories(typeSelect.value);

	// เปลี่ยนรายรับ / รายจ่าย
	typeSelect.addEventListener('change', () => {
		loadCategories(typeSelect.value);
	});
	</script>


<?php include '../vendor/footer.php'; ?>
</body>
</html>

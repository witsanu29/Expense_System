<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/admin_only.php';

	/* ===== ต้อง login เท่านั้น ===== */
	if (!isset($_SESSION['user_id'])) {
		header('Location: ../login.php');
		exit;
	}

	$user_id = $_SESSION['user_id'];
	$error = '';
	$success = '';

	/* ===== เพิ่มหมวด ===== */
	if (isset($_POST['add'])) {
		$type = $_POST['type'];
		$name = trim($_POST['name']);

		if ($name === '') {
			$error = 'กรุณากรอกชื่อหมวด';
		} else {
			$stmt = $conn->prepare("
				INSERT INTO categories (user_id, type, name)
				VALUES (?, ?, ?)
			");
			$stmt->bind_param("iss", $user_id, $type, $name);

			if ($stmt->execute()) {
				$success = '✅ เพิ่มหมวดเรียบร้อย';
			} else {
				$error = '❌ หมวดนี้มีอยู่แล้ว';
			}
		}
	}

	/* ===== แก้ไขหมวด ===== */
	if (isset($_POST['update'])) {
		$id   = (int)$_POST['id'];
		$type = $_POST['type'];
		$name = trim($_POST['name']);

		if ($name === '') {
			$error = 'กรุณากรอกชื่อหมวด';
		} else {
			$stmt = $conn->prepare("
				UPDATE categories
				SET type = ?, name = ?
				WHERE id = ? AND user_id = ?
			");
			$stmt->bind_param("ssii", $type, $name, $id, $user_id);
			$stmt->execute();

			$success = '✅ แก้ไขหมวดเรียบร้อย';
		}
	}

	/* ===== ลบหมวด ===== */
	if (isset($_POST['delete'])) {
		$id = (int)$_POST['id'];

		$stmt = $conn->prepare("
			DELETE FROM categories
			WHERE id = ? AND user_id = ?
		");
		$stmt->bind_param("ii", $id, $user_id);
		$stmt->execute();

		header("Location: categories.php");
		exit;
	}

	/* ===== ดึงหมวด ===== */
	$stmt = $conn->prepare("
		SELECT id, type, name, created_at
		FROM categories
		WHERE user_id = ?
		ORDER BY type, name
	");
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
?>


<head>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar.php'; ?>

			<div class="container-fluid px-4 py-4">

			<h4 class="mb-3">
				<i class="bi bi-tags"></i> จัดการหมวดหมู่
			</h4>

			<?php if ($error): ?>
			<div class="alert alert-danger"><?= $error ?></div>
			<?php endif; ?>

			<?php if ($success): ?>
			<div class="alert alert-success"><?= $success ?></div>
			<?php endif; ?>

			<!-- ===== ฟอร์มเพิ่มหมวด ===== -->
			<div class="card mb-3 shadow-sm">
			<div class="card-body">
			<form method="post" class="row g-3 align-items-end">

				<div class="col-md-3">
					<label class="form-label">ประเภท</label>
					<select name="type" class="form-select" required>
						<option value="income">รายรับ</option>
						<option value="expense">รายจ่าย</option>
					</select>
				</div>

				<div class="col-md-6">
					<label class="form-label">ชื่อหมวด</label>
					<input type="text" name="name" class="form-control" required>
				</div>

				<div class="col-md-3">
					<button name="add" class="btn btn-primary w-100">
						<i class="bi bi-plus-circle"></i> เพิ่มหมวด
					</button>
				</div>

			</form>
			</div>
			</div>

			<!-- ===== ตารางหมวด ===== -->
			<div class="card shadow-sm">
			<div class="card-body table-responsive">

			<table class="table table-hover align-middle">
			<thead class="table-light">
			<tr>
				<th width="60">ลำดับ</th>
				<th>ประเภท</th>
				<th>ชื่อหมวด</th>
				<th width="120">จัดการ</th>
			</tr>
			</thead>
			<tbody>

			<?php $i=1; while ($row = $result->fetch_assoc()): ?>
			<tr>
			<form method="post">
				<td><?= $i++ ?></td>

				<td>
					<select name="type" class="form-select form-select-sm">
						<option value="income" <?= $row['type']=='income'?'selected':'' ?>>รายรับ</option>
						<option value="expense" <?= $row['type']=='expense'?'selected':'' ?>>รายจ่าย</option>
					</select>
				</td>

				<td>
					<input type="text"
						   name="name"
						   class="form-control form-control-sm"
						   value="<?= htmlspecialchars($row['name']) ?>"
						   required>
				</td>

				<td class="text-center">
					<input type="hidden" name="id" value="<?= $row['id'] ?>">

					<!-- ✅ ปุ่มแก้ไข -->
					<button name="update" class="btn btn-sm btn-warning">
						<i class="bi bi-pencil-square"></i>
					</button>

					<!-- ❌ ปุ่มลบ -->
					<button name="delete"
							class="btn btn-sm btn-danger"
							onclick="return confirm('ลบหมวดนี้?')">
						<i class="bi bi-trash"></i>
					</button>
				</td>
			</form>
			</tr>
			<?php endwhile; ?>


			</tbody>
			
			</table>

			</div>
			</div>

</div>

<?php include '../vendor/footer.php'; ?>
</body>
</html>

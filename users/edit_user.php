<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/admin_only.php';

/* ===== admin เท่านั้น ===== */
if ($_SESSION['role'] !== 'admin') {
    die('⛔ หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ไม่พบผู้ใช้');
}

/* ===== ดึงข้อมูลผู้ใช้ ===== */
$stmt = $conn->prepare("
    SELECT id, username, fullname, role
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die('ไม่พบผู้ใช้');
}

/* ❌ ห้ามแก้ไขตัวเอง */
if ($user['id'] == $_SESSION['user_id']) {
    die('⛔ ไม่สามารถแก้ไขบัญชีของตนเองได้');
}

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST['save'])) {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $role     = $_POST['role'];

    if (!in_array($role, ['admin','user','demo'])) {
        die('ค่า role ไม่ถูกต้อง');
    }

    $stmt = $conn->prepare("
        UPDATE users
        SET username=?, fullname=?, role=?
        WHERE id=?
    ");
    $stmt->bind_param("sssi", $username, $fullname, $role, $id);
    $stmt->execute();

    header("Location: user-manager.php");
    exit;
}
?>


<head>
<meta charset="utf-8">
<title>แก้ไขผู้ใช้</title>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

		<div class="container-fluid px-4 py-4">
		<div class="card shadow-sm">
			<div class="card-body">

				<h5 class="mb-4">
					<i class="bi bi-pencil-square"></i> แก้ไขข้อมูลผู้ใช้
				</h5>

			<form method="post">

				<div class="mb-3">
					<label class="form-label">Username</label>
					<input type="text" name="username"
						   class="form-control"
						   value="<?= htmlspecialchars($user['username']) ?>" required>
				</div>

				<div class="mb-3">
					<label class="form-label">ชื่อ-นามสกุล</label>
					<input type="text" name="fullname"
						   class="form-control"
						   value="<?= htmlspecialchars($user['fullname']) ?>" required>
				</div>

				<div class="mb-3">
					<label class="form-label">สิทธิ์ผู้ใช้</label>
					<select name="role" class="form-select" required>
						<option value="demo"  <?= $user['role']=='demo'?'selected':'' ?>>Demo</option>
						<option value="user"  <?= $user['role']=='user'?'selected':'' ?>>User</option>
						<option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
					</select>
				</div>

				<div class="d-flex gap-2">
					<button name="save" class="btn btn-primary">
						💾 บันทึก
					</button>

					<a href="user-manager.php" class="btn btn-secondary">
						↩ กลับ
					</a>
				</div>

			</form>

			</div>
		</div>
	</div>

	
<?php include '../vendor/footer.php'; ?>
</body>
</html>

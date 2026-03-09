<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
// require '../includes/admin_only.php';

/* ===== ต้องเป็น admin เท่านั้น ===== */
if ($_SESSION['role'] !== 'admin') {
    die('⛔ หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ไม่พบผู้ใช้');
}

/* ❌ ห้ามแก้ role ตัวเอง */
if ($id === $_SESSION['user_id']) {
    die('⛔ ไม่สามารถเปลี่ยนสิทธิ์ของตนเองได้');
}

/* ===== ดึงข้อมูล user ===== */
$stmt = $conn->prepare("SELECT id, username, fullname, role FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die('ไม่พบผู้ใช้');
}

/* ===== บันทึก role ===== */
if (isset($_POST['save'])) {
    $role = $_POST['role'];

	if (!in_array($role, ['admin','user','demo'])) {
		die('ค่า role ไม่ถูกต้อง');
	}

    $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->bind_param("si", $role, $id);
    $stmt->execute();

    header("Location: user-manager.php");
    exit;
}
?>



<head>
<meta charset="utf-8">
<title>เปลี่ยนสิทธิ์ผู้ใช้</title>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

		<div class="container mt-5">
			<div class="card shadow-sm">
				<div class="card-body">

					<h5 class="mb-3">
						<i class="bi bi-shield-lock"></i> เปลี่ยนสิทธิ์ผู้ใช้
					</h5>

						<p>
						<b>Username:</b> <?= htmlspecialchars($user['username']) ?><br>
						<b>ชื่อ:</b> <?= htmlspecialchars($user['fullname']) ?>
						</p>

						<form method="post">
							<label class="form-label">สิทธิ์ผู้ใช้</label>

							<select name="role" class="form-select" required>
								<option value="demo"  <?= $user['role']=='demo'?'selected':'' ?>>Demo</option>
								<option value="user"  <?= $user['role']=='user'?'selected':'' ?>>User</option>
								<option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
							</select>

							<div class="mt-4">
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

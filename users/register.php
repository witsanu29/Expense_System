<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
// require '../includes/admin_only.php';

$error = '';
$success = '';

if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if ($username === '' || $fullname === '' || $password === '') {
        $error = '❌ กรุณากรอกข้อมูลให้ครบ';
    } elseif (strlen($username) < 3) {
        $error = '❌ ชื่อผู้ใช้ต้องอย่างน้อย 3 ตัวอักษร';
    } elseif (strlen($password) < 6) {
        $error = '❌ รหัสผ่านต้องอย่างน้อย 6 ตัวอักษร';
    } elseif ($password !== $confirm) {
        $error = '❌ รหัสผ่านไม่ตรงกัน';
    } else {

		$role = 'demo'; // สมัครเอง = ดูอย่างเดียว

        /* ตรวจ username ซ้ำ */
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $error = '❌ ชื่อผู้ใช้นี้ถูกใช้แล้ว';
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $provider = 'local';
            $provider_id = NULL;

			$stmt = $conn->prepare("
				INSERT INTO users
				(username, password, fullname, provider, provider_id, role)
				VALUES (?,?,?,?,?,?)
			");

			$stmt->bind_param(
				"ssssss",
				$username,
				$hash,
				$fullname,
				$provider,
				$provider_id,
				$role
			);

            $stmt->execute();

            $success = '✅ สมัครสมาชิกสำเร็จ สามารถเข้าสู่ระบบได้';
        }
    }
}

?>


<head>
<meta charset="utf-8">
<title>สมัครสมาชิก</title>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-4">

				<div class="card shadow-sm">
					<div class="card-header bg-success text-white text-center">
						<i class="bi bi-person-plus"></i> สมัครสมาชิก
					</div>

						<div class="card-body">

						<?php if ($error): ?>
						<div class="alert alert-danger"><?= $error ?></div>
						<?php endif; ?>

						<?php if ($success): ?>
						<div class="alert alert-success"><?= $success ?></div>
						<a href="login.php" class="btn btn-primary w-100">เข้าสู่ระบบ</a>
						<?php else: ?>

							<form method="post">
							<div class="mb-3">
							<label class="form-label">ชื่อผู้ใช้</label>
							<input type="text" name="username" class="form-control" required>
							</div>

							<div class="mb-3">
							<label class="form-label">ชื่อ–นามสกุล</label>
							<input type="text" name="fullname" class="form-control" required>
							</div>

							<div class="mb-3">
							<label class="form-label">รหัสผ่าน</label>
							<input type="password" name="password" class="form-control" required>
							</div>

							<div class="mb-3">
							<label class="form-label">ยืนยันรหัสผ่าน</label>
							<input type="password" name="confirm" class="form-control" required>
							</div>

							<button name="register" class="btn btn-success w-100">
							สมัครสมาชิก
							</button>
							</form>

						<?php endif; ?>

						</div>
				</div>

			<div class="text-center mt-3">
			<a href="../login.php">← กลับหน้า Login</a>
			</div>

			</div>
		</div>
	</div>

	
<?php include '../vendor/footer.php'; ?>
</body>
</html>

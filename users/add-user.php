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

$error = '';
$success = '';

/* ===== บันทึกผู้ใช้ใหม่ ===== */
if (isset($_POST['save'])) {

    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'] ?? 'demo';

    /* 🔒 ตรวจสิทธิ์เพิ่ม admin */
    if ($_SESSION['role'] !== 'admin' && $role === 'admin') {
        $error = '⛔ ไม่มีสิทธิ์เพิ่มผู้ดูแลระบบ';
    }
    elseif ($password !== $confirm) {
        $error = '❌ รหัสผ่านไม่ตรงกัน';
    }
    else {

        /* ตรวจ username ซ้ำ */
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = '❌ Username นี้ถูกใช้แล้ว';
        }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (username, password, fullname, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $username, $hash, $fullname, $role);
            $stmt->execute();

            $success = '✅ เพิ่มผู้ใช้เรียบร้อยแล้ว';
        }
    }
}
?>



<head>
<meta charset="utf-8">
<title>เพิ่มผู้ใช้</title>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

<div class="container-fluid px-4 py-4">

    <h4 class="mb-3">
        <i class="bi bi-person-plus"></i> เพิ่มผู้ใช้ใหม่
    </h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
    <div class="card-body">

        <form method="post">

            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">ชื่อ-นามสกุล</label>
                <input type="text" name="fullname"
                       class="form-control" required>
            </div>
			
			<div class="mb-3">
				<label class="form-label fw-semibold">สิทธิ์ผู้ใช้</label>
				<select name="role" class="form-select" required>
					<option value="demo">👁 demo (ดูอย่างเดียว)</option>
					<option value="user">👨‍💼 user (ใช้งานระบบ)</option>
					<option value="admin">🛠 admin (ผู้ดูแล)</option>
				</select>
			</div>

            <div class="mb-3">
                <label class="form-label fw-semibold">รหัสผ่าน</label>
                <input type="password" name="password"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password"
                       class="form-control" required>
            </div>

            <div class="d-flex gap-2">
                <button name="save" class="btn btn-success">
                    <i class="bi bi-save"></i> บันทึก
                </button>

                <a href="user-manager.php" class="btn btn-secondary">
                    กลับ
                </a>
            </div>

        </form>

    </div>
    </div>

</div>

<?php include '../vendor/footer.php'; ?>
</body>
</html>

<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';

/* ===== อนุญาตเฉพาะ admin ===== */
if ($_SESSION['role'] !== 'admin') {
    die('⛔ ไม่มีสิทธิ์เข้าถึงหน้านี้');
}

/* ===== เมื่อกดปุ่มลบ ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {

        // ลบข้อมูลทั้งหมด
        if ($conn->query("DELETE FROM transactions")) {

            // รีเซ็ต AUTO_INCREMENT
            $conn->query("ALTER TABLE transactions AUTO_INCREMENT = 1");

            // ✅ redirect ไปหน้า index.php
            header("Location: ../index.php?clear=success");
            exit;

        } else {
            die('❌ เกิดข้อผิดพลาด: ' . $conn->error);
        }

    } else {
        header("Location: ../index.php?clear=cancel");
        exit;
    }
}

?>


<head>
    <meta charset="UTF-8">
    <title>ลบข้อมูลรายรับ–รายจ่าย</title>
<head>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

<div class="container mt-5">
    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-trash3"></i> ลบข้อมูลรายรับ–รายจ่ายทั้งหมด
        </div>

        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="alert alert-warning">
                ⚠ การดำเนินการนี้จะ <strong>ลบข้อมูลทั้งหมด</strong> และไม่สามารถกู้คืนได้
            </div>

            <form method="post" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลทั้งหมด?');">
                <input type="hidden" name="confirm" value="yes">

                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="bi bi-exclamation-triangle"></i> ยืนยันลบข้อมูลทั้งหมด
                </button>

                <a href="../index.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="bi bi-arrow-left"></i> กลับหน้าหลัก
                </a>
            </form>

        </div>
    </div>
</div>

</body>
</html>

<?php
// ===== admin_only.php =====
// ใช้ตรวจสอบสิทธิ์ผู้ดูแลระบบ (Admin only)

// ต้องเรียก session มาก่อน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ถ้าไม่ได้ login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// ❌ ไม่ใช่ admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ?>
    <!doctype html>
    <html lang="th">
    <head>
        <meta charset="utf-8">
        <title>Permission Denied</title>
        <script>
            alert("⛔ หน้านี้สำหรับผู้ดูแลระบบเท่านั้น");
            history.back();
        </script>
    </head>
    <body></body>
    </html>
    <?php
    exit;
}

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    http_response_code(403);
    exit('⛔ หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
}


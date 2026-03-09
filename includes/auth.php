<?php
// ===== เริ่ม session =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===== ตั้งค่าเวลา timeout (20 นาที) ===== */
$timeout = 20 * 60; // 1200 วินาที

/* ===== ตรวจสอบการ login ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* ===== ตรวจสอบ timeout ===== */
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {

    // หมดเวลา → ล้าง session
    session_unset();
    session_destroy();

    header("Location: ../login.php?timeout=1");
    exit();
}

/* ===== อัปเดตเวลาการใช้งานล่าสุด ===== */
$_SESSION['LAST_ACTIVITY'] = time();

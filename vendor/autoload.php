<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* โหลด config */
require_once __DIR__ . '/../includes/config.php';

/* โหลด composer autoload (แก้ path ให้ถูก) */
$vendorAutoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    die('❌ ไม่พบ vendor/autoload.php (กรุณาติดตั้ง composer)');
}

require_once $vendorAutoload;


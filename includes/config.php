<?php
date_default_timezone_set("Asia/Bangkok");

/* ===============================
   TELEGRAM CONFIG
   =============================== */
define('TG_TOKEN', '8477072509:AAEcBfD_0mBErAcfKt6S0Lsz4JoDpUzj0LM');
define('TG_CHAT',  '7297350083');

/* ===============================
   DATABASE CONFIG
   =============================== */
$db_host = "127.0.0.1";
$db_user = "sa";
$db_pass = "sa";
$db_name = "finance_db";

/* ===============================
   SYSTEM LOG
   =============================== */
function log_db($status, $message)
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/system.log';
    $date = date("Y-m-d H:i:s");
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    file_put_contents(
        $logFile,
        "[{$date}] [{$ip}] {$status} : {$message}" . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

/* ===============================
   DATABASE CONNECTION
   =============================== */
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    log_db("DB_ERROR", $conn->connect_error);
    die("❌ เชื่อมต่อฐานข้อมูลไม่สำเร็จ");
}

$conn->set_charset("utf8mb4");

<?php
session_start();

/* ล้าง session ทั้งหมด */
session_unset();
session_destroy();

/* กลับหน้า login */
header("Location: login.php?logout=1");
exit();

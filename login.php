<?php
session_start();
require 'includes/config.php';

/* ===============================
   TELEGRAM FUNCTION
   =============================== */
function sendTelegram($token, $chat_id, $message)
{
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    @file_get_contents($url . '?' . http_build_query($data));
}

/* ===============================
   ถ้า login แล้ว
   =============================== */
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

/* ===============================
   LOGIN PROCESS
   =============================== */
if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT id, username, password, role
        FROM users
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            /* ===== SESSION ===== */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['LAST_ACTIVITY'] = time();

            /* ===== EXTRA INFO ===== */
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                ? 'https'
                : 'http';

            $site_url = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'UNKNOWN');

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR']
                ?? 'UNKNOWN';

            $date = date('d/m/Y');
            $time = date('H:i:s');

            /* ===== MESSAGE ===== */
            $msg = "🔐 <b>เข้าสู่ระบบรายรับ–รายจ่าย</b>\n"
                 . "🌐 เว็บไซต์: {$site_url}\n"
                 . "👤 ผู้ใช้: {$user['username']}\n"
                 . "🛂 สิทธิ์: {$user['role']}\n"
                 . "🌍 IP: {$ip}\n"
                 . "📅 วันที่: {$date}\n"
                 . "🕒 เวลา: {$time}";

            sendTelegram(TG_TOKEN, TG_CHAT, $msg);

            header("Location: index.php");
            exit();
        }
    }

    $error = '❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}
?>




<head>
<meta charset="utf-8">
<title>เข้าสู่ระบบ | ระบบรายรับ–รายจ่าย</title>

<style>
    body {
        background: linear-gradient(135deg, #cfeee3, #e8f7f1);
        font-family: "Segoe UI", system-ui, -apple-system;
    }

    .login-wrapper {
        min-height: 100vh;
    }

    .login-card {
        border-radius: 1.25rem;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,.97);
        border: none;
    }

    .login-card .card-header {
        border-radius: 1.25rem 1.25rem 0 0;
        background: linear-gradient(135deg, #5bbf9c, #7ed6b3);
    }

    .system-title {
        font-weight: 600;
        letter-spacing: .5px;
        color: #2f7d64;
    }

    .form-control:focus {
        box-shadow: 0 0 0 .15rem rgba(91,191,156,.25);
        border-color: #5bbf9c;
    }

    .btn-primary {
        background: linear-gradient(135deg, #5bbf9c, #7ed6b3);
        border: none;
    }

    .btn-primary:hover {
        opacity: .9;
    }

    .input-group-text {
        background-color: #f1faf7;
    }
	
	/* logo */
.logo-circle {
    width: 130px;      /* จาก 90 → 130 */
    height: 130px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
}

.logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}

</style>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-4 col-md-6 col-sm-10">

        <!-- ชื่อระบบ -->
        <div class="text-center mb-4">

			<div class="logo-circle">
				<img src="image/logo2.png" alt="Logo">
			</div>

			<h5 class="system-title mb-1">💰 ระบบจัดการ รายรับ–รายจ่าย</h5>
			<div class="small text-muted">Income–Expense Management System</div>
		</div>

        <div class="card login-card shadow-lg">

            <div class="card-header text-white text-center py-3">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock"></i> เข้าสู่ระบบ
                </h5>
            </div>

            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center small">
                        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['timeout'])): ?>
                    <div class="alert alert-warning text-center small">
                        ⏰ หมดเวลาใช้งาน กรุณาเข้าสู่ระบบใหม่
                    </div>
                <?php endif; ?>

                <form method="post" autocomplete="off">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อผู้ใช้</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text"
                                   name="username"
                                   class="form-control"
                                   placeholder="กรอกชื่อผู้ใช้"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">รหัสผ่าน</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="••••••••"
                                   required>
                        </div>
                    </div>

                    <button name="login"
                            class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right"></i>
                        เข้าสู่ระบบ
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

</body>
</html>

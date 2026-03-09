<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

	<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
		<div class="container-fluid px-4">

			<!-- ชื่อระบบ -->
			<a class="navbar-brand fw-semibold" href=" ">
				<i class="bi bi-wallet2"></i> ระบบรายรับ–รายจ่าย
			</a>

			<!-- ปุ่มมือถือ -->
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
				<span class="navbar-toggler-icon"></span>
			</button>

			<!-- เมนู -->
			<div class="collapse navbar-collapse" id="mainMenu">
				<ul class="navbar-nav ms-auto mb-2 mb-lg-0">

					<li class="nav-item">
						<a class="nav-link" href="../index.php">
							<i class="bi bi-house"></i> หน้าหลัก
						</a>
					</li>

					<!-- 🔥 เมนูรายงาน -->
					<li class="nav-item">
						<a class="nav-link" href="../report/report.php">
							<i class="bi bi-bar-chart-line"></i> รายงาน
						</a>
					</li>
					
					<?php if (isset($_SESSION['user_id'])): ?>

						<!-- ===== เมนูผู้ใช้ ===== -->
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
								<i class="bi bi-person-circle"></i>
								<?= htmlspecialchars($_SESSION['username']) ?>
							</a>
							<ul class="dropdown-menu dropdown-menu-end">
								<li>
									<a class="dropdown-item text-danger" href="../logout.php">
										<i class="bi bi-box-arrow-right"></i> ออกจากระบบ
									</a>
								</li>
							</ul>
						</li>

						<!-- ===== เมนูตั้งค่าระบบ (แสดงเมื่อ login) ===== -->
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<i class="bi bi-gear"></i> ตั้งค่าระบบ
							</a>
							<ul class="dropdown-menu dropdown-menu-end">
								<li>
									<a class="dropdown-item" href="user-manager.php">
										<i class="bi bi-people"></i> จัดการผู้ใช้
									</a>
								</li>
								
								<li>
									<a class="dropdown-item" href="../Expense/categories.php">
										<i class="bi bi-people"></i> จัดการหมวดหมู่
									</a>
								</li>
							</ul>
						</li>

					<?php else: ?>

						<!-- ===== ยังไม่ login ===== -->
						<li class="nav-item">
							<a class="nav-link" href="../login.php">
								<i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
							</a>
						</li>

					<?php endif; ?>

				</ul>
			</div>

		</div>
	</nav>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
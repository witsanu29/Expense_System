<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/admin_only.php';

/* ===== ลบผู้ใช้ ===== */
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $current_user_id = $_SESSION['user_id'];

    // ❌ ห้ามลบตัวเอง
    if ($id === $current_user_id) {
        die('⛔ ไม่สามารถลบบัญชีของตนเองได้');
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: user-manager.php");
    exit;
}


/* ===== แก้ไขข้อมูล ===== */
if (isset($_POST['update'])) {
    $id       = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);

    $stmt = $conn->prepare("
        UPDATE users SET username=?, fullname=? WHERE id=?
    ");
    $stmt->bind_param("ssi", $username, $fullname, $id);
    $stmt->execute();

    header("Location: user-manager.php");
    exit;
}

/* ===== เปลี่ยนรหัสผ่าน ===== */
if (isset($_POST['change_password'])) {

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        die('❌ รหัสผ่านไม่ตรงกัน');
    }

    $id   = (int)$_POST['id'];
    $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users SET password=? WHERE id=?
    ");
    $stmt->bind_param("si", $pass, $id);
    $stmt->execute();

    header("Location: user-manager.php");
    exit;
}


/* ===== ดึงผู้ใช้ ===== */
$result = $conn->query("
    SELECT id, username, fullname, role
		FROM users
		ORDER BY id DESC
");

?>


<head>
<meta charset="utf-8">
<title>จัดการผู้ใช้</title>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar1.php'; ?>

		<div class="container-fluid px-4 py-4">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h4 class="mb-0">
				<i class="bi bi-people"></i> จัดการผู้ใช้
			</h4>

			<div class="d-flex gap-2">
				<a href="add-user.php" class="btn btn-success">
					<i class="bi bi-person-plus"></i> เพิ่มผู้ใช้
				</a>

				<button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
					<i class="bi bi-trash3"></i> ลบตาราง (สำคัญ)
				</button>
			</div>
			
			<div class="modal fade" id="deleteModal" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content border-danger">

						<div class="modal-header bg-danger text-white">
							<h5 class="modal-title">
								<i class="bi bi-exclamation-triangle"></i> ยืนยันการลบข้อมูล
							</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
						</div>

						<div class="modal-body">
							⚠ ข้อมูลรายรับ–รายจ่ายทั้งหมดจะถูกลบ และไม่สามารถกู้คืนได้
						</div>

						<div class="modal-footer">
							<form action="../Expense/clear_transactions.php" method="post">
								<input type="hidden" name="confirm" value="yes">
								<button type="submit" class="btn btn-danger">
									<i class="bi bi-trash3"></i> ยืนยันลบ
								</button>
							</form>

							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
								ยกเลิก
							</button>
						</div>

					</div>
				</div>
			</div>


		</div>



		<div class="card shadow-sm">
		<div class="card-body table-responsive">

		<table class="table table-bordered align-middle">
		<thead class="table-light">
		<tr>
			<th width="60">ID</th>
			<th>Username</th>
			<th>ชื่อ-นามสกุล</th>
			<th width="220">การจัดการ</th>
			<th width="220">สถานะ</th>
		</tr>
		</thead>
		
			<tbody>

			<?php while ($row = $result->fetch_assoc()): ?>

			<!-- ===== แถวแก้ไขข้อมูลผู้ใช้ ===== -->
			<tr>
			<form method="post">
				<td><?= $row['id'] ?></td>

				<td>
					<input name="username" class="form-control"
						   value="<?= htmlspecialchars($row['username']) ?>" required>
				</td>

				<td>
					<input name="fullname" class="form-control"
						   value="<?= htmlspecialchars($row['fullname']) ?>" required>
				</td>

				<td class="text-center">

				<?php if ($row['id'] == $_SESSION['user_id']): ?>

					<!-- 🚫 แก้ไขตัวเอง -->
					<button type="button"
							class="btn btn-sm btn-warning me-1"
							onclick="alertSelfAction()"
							title="ไม่สามารถแก้ไขตัวเองได้">
						<i class="bi bi-pencil-square"></i>
					</button>

					<!-- 🚫 เปลี่ยนรหัสผ่านตัวเอง -->
					<button type="button"
							class="btn btn-sm btn-secondary me-1"
							onclick="alertSelfAction()"
							title="ไม่สามารถเปลี่ยนรหัสผ่านตัวเองได้">
						<i class="bi bi-key"></i>
					</button>

					<!-- 🚫 ลบตัวเอง -->
					<button type="button"
							class="btn btn-sm btn-danger"
							onclick="alertSelfAction()"
							title="ไม่สามารถลบตัวเองได้">
						<i class="bi bi-trash"></i>
					</button>

				<?php else: ?>

					<!-- 🔧 แก้ไขผู้ใช้อื่น -->
					<a href="edit_user.php?id=<?= $row['id'] ?>"
					   class="btn btn-sm btn-warning me-1"
					   title="แก้ไขข้อมูล">
						<i class="bi bi-pencil-square"></i>
					</a>

					<!-- 🔑 เปลี่ยนรหัสผ่าน -->
					<button type="button"
							class="btn btn-sm btn-secondary me-1"
							title="เปลี่ยนรหัสผ่าน"
							onclick="document.getElementById('passform<?= $row['id'] ?>').classList.toggle('d-none')">
						<i class="bi bi-key"></i>
					</button>

					<!-- 🗑 ลบผู้ใช้ -->
					<form method="post" class="d-inline">
						<input type="hidden" name="id" value="<?= $row['id'] ?>">
						<button name="delete"
								class="btn btn-sm btn-danger"
								onclick="return confirm('ยืนยันการลบผู้ใช้นี้?')"
								title="ลบผู้ใช้">
							<i class="bi bi-trash"></i>
						</button>
					</form>

				<?php endif; ?>

				</td>

				<td class="text-center">
					<?php if ($row['role'] === 'admin'): ?>
						<span class="badge bg-danger">Admin</span>
					<?php elseif ($row['role'] === 'demo'): ?>
						<span class="badge bg-warning text-dark">Demo</span>
					<?php else: ?>
						<span class="badge bg-secondary">User</span>
					<?php endif; ?>
				</td>

			</form>
			</tr>

			<!-- ===== แถวเปลี่ยนรหัสผ่าน (ซ่อน) ===== -->
			<tr id="passform<?= $row['id'] ?>" class="d-none bg-light">
			<td colspan="5">

			<form method="post" class="row g-2 align-items-end">
				<input type="hidden" name="id" value="<?= $row['id'] ?>">

				<div class="col-md-4">
					<label class="form-label">รหัสผ่านใหม่</label>
					<input type="password" name="new_password"
						   class="form-control" required>
				</div>

				<div class="col-md-4">
					<label class="form-label">ยืนยันรหัสผ่าน</label>
					<input type="password" name="confirm_password"
						   class="form-control" required>
				</div>

				<div class="col-md-4">
					<button name="change_password"
							class="btn btn-success mt-4">
						<i class="bi bi-save"></i> บันทึก
					</button>
				</div>
			</form>

			</td>
			</tr>

			<?php endwhile; ?>

			</tbody>

		
		</table>

		</div>
		</div>
		</div>

		<!-- 🚫 แจ้งเตือนแก้ไข/ลบตัวเอง -->
		<div class="modal fade" id="selfActionModal" tabindex="-1">
		  <div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
			  <div class="modal-header bg-warning-subtle">
				<h5 class="modal-title">
				  <i class="bi bi-exclamation-triangle text-warning"></i>
				  ไม่สามารถดำเนินการได้
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			  </div>
			  <div class="modal-body text-center">
				<p class="mb-0">
				  👤 <strong>ผู้ดูแลระบบไม่สามารถแก้ไขหรือลบข้อมูลของตนเองได้</strong><br>
				  เพื่อความปลอดภัยของระบบ
				</p>
			  </div>
			  <div class="modal-footer">
				<button class="btn btn-secondary" data-bs-dismiss="modal">
				  รับทราบ
				</button>
			  </div>
			</div>
		  </div>
		</div>

		<script>
		function alertSelfAction() {
			new bootstrap.Modal(document.getElementById('selfActionModal')).show();
		}
		</script>

		<script>
		function togglePass(id, btn) {
			const input = document.getElementById(id);
			const icon  = btn.querySelector('i');

			if (input.type === 'password') {
				input.type = 'text';
				icon.classList.remove('bi-eye');
				icon.classList.add('bi-eye-slash');
			} else {
				input.type = 'password';
				icon.classList.remove('bi-eye-slash');
				icon.classList.add('bi-eye');
			}
		}
		</script>

<?php include '../vendor/footer.php'; ?>
</body>
</html>

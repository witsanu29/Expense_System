<?php
session_start();
require 'includes/config.php';

/* ===== auth guard ===== */
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'];

$year  = date('Y');
$month = date('n');

/* ===== ฟังก์ชันแปลงวันที่ พ.ศ. ===== */
function thaiDate($date){
    $t = strtotime($date);
    return date('d/m/', $t) . (date('Y', $t) + 543);
}


// ดึงรายการตาม role
$rows = [];

if ($role === 'admin') {
		// ✅ admin เห็นทุกคน
		$stmt = $conn->prepare("
			SELECT t.*, u.fullname
			FROM transactions t
			LEFT JOIN users u ON t.user_id = u.id
			ORDER BY t.trans_date DESC
		");
		$stmt->execute();


	} else {
		// ✅ user + demo เห็นเฉพาะของตัวเอง
		$stmt = $conn->prepare("
			SELECT t.*, u.fullname
			FROM transactions t
			LEFT JOIN users u ON t.user_id = u.id
			WHERE t.user_id = ?
			ORDER BY t.trans_date DESC
		");
		$stmt->bind_param("i", $user_id);
		$stmt->execute();

	}


	/* ===== ดึง ===== */
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

/* ===== รวมยอด ===== */
$total_income  = 0;
$total_expense = 0;

foreach ($rows as $r) {
    if ($r['type'] === 'income')  $total_income  += $r['amount'];
    if ($r['type'] === 'expense') $total_expense += $r['amount'];
}

$balance = $total_income - $total_expense;


	/* ===== งบประมาณ ===== */
	$sql = "
	SELECT SUM(amount) AS total_expense
	FROM transactions
	WHERE user_id = ?
	AND type = 'expense'
	AND YEAR(trans_date) = ?
	AND MONTH(trans_date) = ?
	";

	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iii", $user_id, $year, $month);
	$stmt->execute();

	$row = $stmt->get_result()->fetch_assoc();
	$month_expense = $row['total_expense'] ?? 0;


	$budget = 15000;

	/* ===== สถานะงบ ===== */
	$percent = ($budget > 0) ? ($month_expense / $budget) * 100 : 0;

	if ($percent < 80) {
		$status = 'success';
		$text   = '✅ ยังอยู่ในงบ';
	} elseif ($percent < 100) {
		$status = 'warning';
		$text   = '⚠️ ใกล้เกินงบ';
	} else {
		$status = 'danger';
		$text   = '❌ เกินงบแล้ว';
	}


?>


<head>
<style>
    body {
        background: #f4f6f9;
    }
    .card {
        border-radius: 1rem;
    }
    .card-header {
        font-weight: 600;
    }
    .summary-icon {
        font-size: 2.2rem;
        opacity: .25;
    }
</style>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar.php'; ?>

		<?php if (isset($_GET['clear']) && $_GET['clear'] === 'success'): ?>
			<div class="alert alert-success">
				✅ ลบข้อมูลรายรับ–รายจ่ายทั้งหมดเรียบร้อยแล้ว
			</div>
		<?php endif; ?>

		<?php if (isset($_GET['clear']) && $_GET['clear'] === 'cancel'): ?>
			<div class="alert alert-warning">
				⚠ ยกเลิกการลบข้อมูล
			</div>
		<?php endif; ?>

		<?php if (!empty($_SESSION['popup_error'])): ?>
		<!-- ===== Modal แจ้งเตือน ===== -->
		<div class="modal fade" id="roleErrorModal" tabindex="-1">
		  <div class="modal-dialog modal-dialog-centered">
			<div class="modal-content border-0 shadow">

			  <div class="modal-header bg-danger text-white">
				<h5 class="modal-title">
				  <i class="bi bi-exclamation-triangle-fill me-2"></i> ไม่สามารถดำเนินการได้
				</h5>
				<button type="button" class="btn-close btn-close-white"
						data-bs-dismiss="modal"></button>
			  </div>

			  <div class="modal-body text-center fs-5 py-4">
				<?= htmlspecialchars($_SESSION['popup_error']) ?>
			  </div>

			  <div class="modal-footer justify-content-center">
				<button type="button" class="btn btn-danger px-4"
						data-bs-dismiss="modal">
				  รับทราบ
				</button>
			  </div>

			</div>
		  </div>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', () => {
			const modal = new bootstrap.Modal(
				document.getElementById('roleErrorModal')
			);
			modal.show();
		});
		</script>
		<?php unset($_SESSION['popup_error']); endif; ?>


<div class="container-fluid px-4 py-4">

		<!-- ===== Header ===== -->
		<div class="d-flex justify-content-between align-items-center mb-4">
			<div>
				<h3 class="fw-bold mb-0">
					<i class="bi bi-wallet2"></i> ระบบรายรับ–รายจ่าย
				</h3>
				<small class="text-muted">สรุปภาพรวมการเงินส่วนตัว</small>
			</div>
			<a href="Expense/add.php" class="btn btn-primary btn-lg">
				<i class="bi bi-plus-circle"></i> เพิ่มรายการ
			</a>
		</div>

		<!-- ===== Summary Cards ===== -->
		<div class="row g-3 mb-4">

		<!-- รายรับ -->
		<div class="col-md-4">
			<div class="card shadow-sm text-white bg-success position-relative">
				<div class="card-body">
					<i class="bi bi-arrow-down-circle summary-icon position-absolute top-0 end-0 m-3"></i>
					<div class="fw-semibold">รายรับรวม</div>
					<h3 class="fw-bold mb-0">
						<?= number_format($total_income,2) ?>
					</h3>
					<small>บาท</small>
				</div>
			</div>
		</div>

		<!-- รายจ่าย -->
		<div class="col-md-4">
			<div class="card shadow-sm text-white bg-danger position-relative">
				<div class="card-body">
					<i class="bi bi-arrow-up-circle summary-icon position-absolute top-0 end-0 m-3"></i>
					<div class="fw-semibold">รายจ่ายรวม</div>
					<h3 class="fw-bold mb-0">
						<?= number_format($total_expense,2) ?>
					</h3>
					<small>บาท</small>
				</div>
			</div>
		</div>

		<!-- คงเหลือ -->
		<div class="col-md-4">
			<div class="card shadow-sm text-white bg-primary position-relative">
				<div class="card-body">
					<i class="bi bi-wallet2 summary-icon position-absolute top-0 end-0 m-3"></i>
					<div class="fw-semibold">คงเหลือ</div>
					<h3 class="fw-bold mb-0">
						<?= number_format($balance,2) ?>
					</h3>
					<small>บาท</small>
				</div>
			</div>
		</div>

		</div>

		<!-- ===== Budget Card ===== -->
		<div class="row mb-4">
		<div class="col-md-4">
			<div class="card shadow-sm h-100">
				<div class="card-body">
					<h6 class="fw-bold mb-2">
						งบประมาณเดือน <?= $month ?>/<?= $year+543 ?>
					</h6>

					<p class="mb-1">
						ใช้ไป <strong><?= number_format($month_expense,2) ?></strong> /
						<?= number_format($budget,2) ?> บาท
					</p>

					<div class="progress mb-2" style="height:20px;">
						<div class="progress-bar bg-<?= $status ?>"
							 style="width: <?= min($percent,100) ?>%">
							 <?= number_format($percent,1) ?>%
						</div>
					</div>

					<div class="alert alert-<?= $status ?> py-2 mb-0 text-center">
						<?= $text ?>
					</div>
				</div>
			</div>
		</div>
		</div>


		<!-- ===== Table ===== -->
		<div class="card shadow-sm">
		<div class="card-header bg-dark text-white">
			<i class="bi bi-table"></i> รายการทั้งหมด
		</div>

		<div class="card-body p-0">
		
		<table class="table table-hover align-middle mb-0">
			<thead class="table-secondary text-center">
			<tr>
				<th style="width:60px;">ลำดับ</th>
				<th>ชื่อผู้บันทึก</th>
				<th>วันที่</th>
				<th>ประเภท</th>
				<th>หมวด</th>
				<th class="text-start">รายละเอียด</th>
				<th class="text-end">จำนวน (บาท)</th>
				
				<?php if ($role !== 'demo'): ?>
				<th style="width:120px;">จัดการ</th>
				<?php endif; ?>
			</tr>
			</thead>
			<tbody>

			<?php if (!empty($rows)): ?>
			<?php $i = 1; ?>
			<?php foreach ($rows as $row): ?>

			<?php
			$canEdit   = false;
			$canDelete = false;

			if ($role === 'admin') {
				$canEdit = true;
				$canDelete = true;
			} elseif ($role === 'user' && $row['user_id'] == $user_id) {
				$canEdit = true;
			}
			?>

			<tr>
				<td class="text-center text-muted"><?= $i++ ?></td>
				<td> <?= htmlspecialchars($row['fullname'] ?? '-') ?> </td>
				<td><?= thaiDate($row['trans_date']) ?></td>
				<td class="text-center">
					<span class="badge rounded-pill bg-<?= $row['type']=='income'?'success':'danger' ?>">
						<i class="bi <?= $row['type']=='income'
							?'bi-arrow-down-left'
							:'bi-arrow-up-right' ?>"></i>
						<?= $row['type']=='income'?'รายรับ':'รายจ่าย' ?>
					</span>
				</td>

				<td><?= htmlspecialchars($row['category']) ?></td>

				<td class="text-muted">
					<?= htmlspecialchars($row['description'] ?: '-') ?>
				</td>

				<td class="text-end fw-bold <?= $row['type']=='income'?'text-success':'text-danger' ?>">
					<?= $row['type']=='income' ? '+' : '-' ?>
					<?= number_format($row['amount'],2) ?>
				</td>

				<?php if ($role !== 'demo'): ?>
				<td class="text-center">
					<?php if ($canEdit): ?>
					<a href="Expense/edit.php?id=<?= $row['id'] ?>"
					   class="btn btn-sm btn-warning">
						<i class="bi bi-pencil"></i>
					</a>
					<?php endif; ?>

					<?php if ($canDelete): ?>
					<a href="delete.php?id=<?= $row['id'] ?>"
					   class="btn btn-sm btn-danger"
					   onclick="return confirm('ยืนยันการลบรายการนี้?')">
						<i class="bi bi-trash"></i>
					</a>
					<?php endif; ?>
				</td>
				<?php endif; ?>

			</tr>

			<?php endforeach; ?>
			<?php else: ?>

			<tr>
				<td colspan="<?= $role !== 'demo' ? 8 : 7 ?>"
					class="text-center text-muted py-4">
					<i class="bi bi-inbox fs-4 d-block mb-2"></i>
					ยังไม่มีข้อมูลรายการ
				</td>
			</tr>

			<?php endif; ?>

			</tbody>

		</table>

		</div>
	</div>

</div>

<?php include 'vendor/footer.php'; ?>
</body>
</html>

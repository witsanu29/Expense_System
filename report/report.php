<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';

	/* ================== SESSION ================== */
	$role    = $_SESSION['role']    ?? 'demo';
	$user_id = $_SESSION['user_id'] ?? 0;

	$canViewSummary = in_array($role, ['admin','user']);
	$canEdit   = in_array($role, ['admin','user']);
	$canDelete = ($role === 'admin');

	/* ================== GET ================== */
	$year     = $_GET['year']     ?? date('Y');
	$month    = $_GET['month']    ?? '';
	$type     = $_GET['type']     ?? '';
	$category = $_GET['category'] ?? '';

	/* ================== WHERE ================== */
	$where  = [];
	$params = [];
	$types  = "";

	/* ---- role ---- */
	if ($role === 'user') {
		$where[]  = "t.user_id = ?";
		$params[] = $user_id;
		$types   .= "i";
	}
	if ($role === 'demo') {
		$where[] = "1=0";
	}

	/* ---- filter ---- */
	if ($year !== '') {
		$where[]  = "YEAR(t.trans_date) = ?";
		$params[] = (int)$year;
		$types   .= "i";
	}
	if ($month !== '') {
		$where[]  = "MONTH(t.trans_date) = ?";
		$params[] = (int)$month;
		$types   .= "i";
	}
	if ($type !== '') {
		$where[]  = "t.type = ?";
		$params[] = $type;
		$types   .= "s";
	}
	if ($category !== '') {
		$where[]  = "t.category = ?";
		$params[] = $category;
		$types   .= "s";
	}

	$whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

	/* ================== MONTH TH ================== */
	$months = [
		1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',
		5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',
		9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'
	];

	/* ================== SUMMARY ================== */
	$sumIncome = $sumExpense = $sumBalance = 0;

	if ($canViewSummary) {
		$sqlSum = "
			SELECT
				SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END) AS income,
				SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
			FROM transactions t
			$whereSql
		";
		$stmt = $conn->prepare($sqlSum) or die($conn->error);
		if ($types) $stmt->bind_param($types, ...$params);
		$stmt->execute();
		$r = $stmt->get_result()->fetch_assoc();

		$sumIncome  = $r['income']  ?? 0;
		$sumExpense = $r['expense'] ?? 0;
		$sumBalance = $sumIncome - $sumExpense;
	}

	/* ================== LIST ================== */
	$sqlList = "
		SELECT
			t.trans_date,
			t.type,
			t.amount,
			u.fullname
		FROM transactions t
		JOIN users u ON u.id = t.user_id
		$whereSql
		ORDER BY t.trans_date DESC
	";
	$stmtList = $conn->prepare($sqlList) or die($conn->error);
	if ($types) $stmtList->bind_param($types, ...$params);
	$stmtList->execute();
	$listResult = $stmtList->get_result();

	/* ================== GROUP ================== */
	$sqlGroup = "
		SELECT
			u.fullname,
			YEAR(t.trans_date) y,
			MONTH(t.trans_date) m,
			SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END) total_income,
			SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) total_expense
		FROM transactions t
		LEFT JOIN users u ON t.user_id = u.id
		$whereSql
		GROUP BY u.fullname, y, m
		ORDER BY y DESC, m DESC
	";
	$stmtGroup = $conn->prepare($sqlGroup) or die($conn->error);
	if ($types) $stmtGroup->bind_param($types, ...$params);
	$stmtGroup->execute();
	$result = $stmtGroup->get_result();
?>


<head>
<meta charset="utf-8">
<title>รายงานรายรับ–รายจ่าย</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style> body { background: #f4f6fa; 
} 
.card-header-report { background: linear-gradient(135deg, #212529, #343a40); 
} 
.table thead th { font-size: .9rem; letter-spacing: .03em; 
} 
.table tbody tr:hover { background-color: #f8f9fa; 
} 
.money { font-variant-numeric: tabular-nums; 
} 
.balance-positive { color: #198754; font-weight: 600; 
} 
.balance-negative { color: #dc3545; font-weight: 600; 
} 
.month-badge { background: #e9ecef; font-weight: 500; 
} 
</style>

<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar.php'; ?>

	<div class="container-fluid px-4 py-4">

		<div class="card shadow-sm border-0">
			<div class="card-header card-header-report text-white fs-5">
				<i class="bi bi-graph-up-arrow me-2"></i>
				รายงานสรุปรายเดือน / รายปี
			</div>

			<div class="card-body">

		<!-- ================= SUMMARY ================= -->
		<?php if ($canViewSummary): ?>
		<div class="card mb-3 border-0 shadow-sm">
			<div class="card-header card-header-report text-white">
				<div class="row text-center g-3">

					<div class="col">
						<div class="summary-box">
							<div class="small opacity-75">รายรับ</div>
							<div class="fs-5 fw-semibold text-success">
								+<?= number_format($sumIncome,2) ?>
							</div>
						</div>
					</div>

					<div class="col">
						<div class="summary-box">
							<div class="small opacity-75">รายจ่าย</div>
							<div class="fs-5 fw-semibold text-danger">
								-<?= number_format($sumExpense,2) ?>
							</div>
						</div>
					</div>

					<div class="col">
						<div class="summary-box">
							<div class="small opacity-75">คงเหลือ</div>
							<div class="fs-5 fw-bold <?= $sumBalance>=0?'text-success':'text-danger' ?>">
								<?= number_format($sumBalance,2) ?>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- ================= FILTER ================= -->
		<div class="card mb-3 border-0 shadow-sm">
		<div class="card-body">

		<form method="get" class="row g-3 align-items-end">

		<div class="col-md-2">
			<label class="form-label small">ปี</label>
			<select name="year" class="form-select">
				<option value="">ทุกปี</option>
				<?php for($y=2030;$y>=2025;$y--): ?>
					<option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>>
						<?= $y+543 ?>
					</option>
				<?php endfor; ?>
			</select>
		</div>

		<div class="col-md-2">
			<label class="form-label small">เดือน</label>
			<select name="month" class="form-select">
				<option value="">ทุกเดือน</option>
				<?php foreach ($months as $m=>$name): ?>
					<option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>>
						<?= $name ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-md-3">
			<label class="form-label small">ประเภท</label>
			<select name="type" class="form-select">
				<option value="">ทั้งหมด</option>
				<option value="income" <?= $type=='income'?'selected':'' ?>>รายรับ</option>
				<option value="expense" <?= $type=='expense'?'selected':'' ?>>รายจ่าย</option>
			</select>
		</div>

		<div class="col-md-3">
			<label class="form-label small">หมวดหมู่</label>
			<select name="category" class="form-select">
				<option value="">ทุกหมวด</option>
				<option value="ค่าไฟ" <?= $category=='ค่าไฟ'?'selected':'' ?>>ค่าไฟ</option>
				<option value="ค่าอาหาร" <?= $category=='ค่าอาหาร'?'selected':'' ?>>ค่าอาหาร</option>
			</select>
		</div>

		<div class="col-md-2 d-grid gap-2">
			<button class="btn btn-primary">
				<i class="bi bi-funnel-fill"></i> แสดงผล
			</button>
			<a href="report.php" class="btn btn-outline-secondary">
				ล้างตัวกรอง
			</a>
		</div>

		</form>
		</div>
		</div>


		<!-- ================= GROUP REPORT (DESKTOP) ================= -->
		<div class="table-responsive d-none d-md-block">
		<table class="table table-bordered align-middle">
		<thead class="table-light text-center">
		<tr>
			<th>ลำดับ</th>
			<th>ผู้ใช้</th>
			<th>ปี</th>
			<th>เดือน</th>
			<th class="text-end text-success">รายรับ</th>
			<th class="text-end text-danger">รายจ่าย</th>
			<th class="text-end">คงเหลือ</th>
		</tr>
		</thead>
		<tbody>

			<?php
			$i=1;
			$result->data_seek(0);
			while ($row = $result->fetch_assoc()):
			$balance = $row['total_income'] - $row['total_expense'];
			?>
			<tr>
				<td class="text-center"><?= $i++ ?></td>
				<td><?= htmlspecialchars($row['fullname']) ?></td>
				<td class="text-center"><?= $row['y']+543 ?></td>
				<td class="text-center">
					<span class="badge bg-primary"><?= $months[$row['m']] ?></span>
				</td>
				<td class="text-end text-success">+<?= number_format($row['total_income'],2) ?></td>
				<td class="text-end text-danger">-<?= number_format($row['total_expense'],2) ?></td>
				<td class="text-end fw-bold <?= $balance>=0?'text-success':'text-danger' ?>">
					<?= number_format($balance,2) ?>
				</td>
			</tr>
			<?php endwhile; ?>

		</tbody>
		</table>
		</div>

		<!-- ================= GROUP REPORT (MOBILE) ================= -->
		<div class="d-md-none">
		<?php
		$result->data_seek(0);
		while ($row = $result->fetch_assoc()):
		$balance = $row['total_income'] - $row['total_expense'];
		$year_th = $row['y'] + 543;
		$pos = $balance>=0;
		?>
		<div class="card shadow-sm mb-3 border-0" style="border-left:6px solid <?= $pos?'#198754':'#dc3545' ?>">
		<div class="card-body">

		<div class="d-flex justify-content-between mb-2">
			<div>
				<div class="fw-bold"><?= $months[$row['m']] ?></div>
				<div class="small text-muted">พ.ศ. <?= $year_th ?></div>
			</div>
			<i class="bi <?= $pos?'bi-arrow-up':'bi-arrow-down' ?> fs-3 <?= $pos?'text-success':'text-danger' ?>"></i>
		</div>

		<div class="row text-center">
			<div class="col">
				<div class="small text-muted">รายรับ</div>
				<div class="text-success fw-semibold">+<?= number_format($row['total_income'],2) ?></div>
			</div>
			<div class="col">
				<div class="small text-muted">รายจ่าย</div>
				<div class="text-danger fw-semibold">-<?= number_format($row['total_expense'],2) ?></div>
			</div>
		</div>

		<hr>

		<div class="text-center fw-bold <?= $pos?'text-success':'text-danger' ?>">
			คงเหลือ <?= number_format($balance,2) ?> บาท
		</div>

		</div>
		</div>
		<?php endwhile; ?>
		</div>

		<?php if ($canEdit): ?>
		<a href="../Expense/add.php"
		   class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow-lg">
			<i class="bi bi-plus fs-4"></i>
		</a>
		<?php endif; ?>

		</div>
		</div>
</div>

<?php include '../vendor/footer.php'; ?>
</body>
</html>

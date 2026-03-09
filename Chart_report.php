<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/admin_only.php'; // ไฟล์เชื่อมฐานข้อมูล

// ===== เลือกปี (ถ้าไม่เลือก ใช้ปีปัจจุบัน) =====
$year = $_GET['year'] ?? date('Y');

// ===== Query สรุป =====
$sql = "
SELECT 
    MONTH(trans_date) AS m,
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
FROM transactions
WHERE YEAR(trans_date) = ?
GROUP BY MONTH(trans_date)
ORDER BY m
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$income = [];
$expense = [];

while ($row = $result->fetch_assoc()) {
    $months[]  = $row['m'];
    $income[]  = $row['total_income'];
    $expense[] = $row['total_expense'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานรายรับ–รายจ่าย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- ===== Navbar ===== -->
<?php require 'head.php'; ?>

	<div class="container-fluid px-4 py-4">

		<h4 class="fw-bold mb-4">
		<i class="bi bi-bar-chart"></i> รายงานรายรับ–รายจ่าย ปี <?= $year+543 ?>
		</h4>

			<!-- 🔍 เลือกปี -->
			<form method="get" class="mb-4">
			<select name="year" class="form-select w-auto d-inline">
			<?php
			for ($y = date('Y'); $y >= date('Y')-5; $y--) {
				echo "<option value='$y' ".($y==$year?'selected':'').">".($y+543)."</option>";
			}
			?>
			</select>
			<button class="btn btn-primary ms-2">ดูรายงาน</button>
			</form>

			<div class="card shadow-sm">
				<div class="card-body">

				<canvas id="financeChart" height="100"></canvas>

				</div>
			</div>

	</div>

<script>
new Chart(document.getElementById('financeChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [
            {
                label: 'รายรับ',
                data: <?= json_encode($income) ?>,
                backgroundColor: '#198754'
            },
            {
                label: 'รายจ่าย',
                data: <?= json_encode($expense) ?>,
                backgroundColor: '#dc3545'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => value.toLocaleString() + ' ฿'
                }
            }
        }
    }
});
</script>

</body>
</html>

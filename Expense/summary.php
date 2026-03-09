<?php
session_start();
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/admin_only.php'; // ต้องมี $conn (mysqli)

?>


<head>
<head>
<!-- ===== head ===== -->
<?php require 'head.php'; ?>

</head>
<body>

<!-- ===== Navbar ===== -->
<?php require 'navbar.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white">
            📊 สรุปรายรับ–รายจ่าย รายเดือน / รายปี
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-secondary text-center">
                    <tr>
                        <th>ปี</th>
                        <th>เดือน</th>
                        <th class="text-end text-success">รายรับรวม</th>
                        <th class="text-end text-danger">รายจ่ายรวม</th>
                        <th class="text-end">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $sql = "
                    SELECT
                        YEAR(trans_date) AS y,
                        MONTH(trans_date) AS m,
                        SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
                        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
                    FROM transactions
                    GROUP BY YEAR(trans_date), MONTH(trans_date)
                    ORDER BY y DESC, m DESC
                ";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $balance = $row['total_income'] - $row['total_expense'];

                        echo '<tr>';
                        echo '<td class="text-center">'.$row['y'].'</td>';
                        echo '<td class="text-center">'.$row['m'].'</td>';
                        echo '<td class="text-end text-success">'.number_format($row['total_income'],2).'</td>';
                        echo '<td class="text-end text-danger">'.number_format($row['total_expense'],2).'</td>';
                        echo '<td class="text-end fw-semibold">'.number_format($balance,2).'</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูล</td></tr>';
                }
                ?>

                </tbody>
            </table>

        </div>
    </div>

</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	
<?php include '../vendor/footer.php'; ?>
</body>
</html>

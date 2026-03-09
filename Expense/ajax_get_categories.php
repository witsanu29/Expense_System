<?php
require '../includes/config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
$role    = $_SESSION['role'] ?? 'demo';
$type    = $_GET['type'] ?? '';

if (!in_array($type, ['income','expense'])) {
    echo json_encode([]);
    exit;
}

if ($role === 'admin') {
    // ✅ admin เห็นทุกหมวด ทุก user
    $stmt = $conn->prepare("
        SELECT DISTINCT category
        FROM transactions
        WHERE type = ?
          AND category <> ''
        ORDER BY category
    ");
    $stmt->bind_param("s", $type);
} else {
    // 👤 user เห็นเฉพาะของตัวเอง
    $stmt = $conn->prepare("
        SELECT DISTINCT category
        FROM transactions
        WHERE user_id = ?
          AND type = ?
          AND category <> ''
        ORDER BY category
    ");
    $stmt->bind_param("is", $user_id, $type);
}

$stmt->execute();
$result = $stmt->get_result();

$cats = [];
while ($row = $result->fetch_assoc()) {
    $cats[] = $row['category'];
}

echo json_encode($cats, JSON_UNESCAPED_UNICODE);

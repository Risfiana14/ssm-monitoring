<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

    $sql = "
        SELECT
            id,
            location,
            device_name,
            device_ip,
            device_type,
            status,
            notes,
            image,
            image_before,
            image_after,
            created_at,
            updated_at
        FROM repair_history
        WHERE 1 = 1
    ";

    $params = [];

    if (!empty($start_date)) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $start_date;
    }

    if (!empty($end_date)) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $end_date;
    }

    $sql .= " ORDER BY updated_at DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error.',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    // Ambil parameter tanggal dari URL
    $start_date = isset($_GET['start_date'])
        ? trim($_GET['start_date'])
        : '';

    $end_date = isset($_GET['end_date'])
        ? trim($_GET['end_date'])
        : '';

    $sql = "
        SELECT
            dh.id,
            dh.location,
            dh.device_name,
            dh.device_ip,
            dh.device_type,
            dh.status,
            dh.notes,
            dh.image,
            dh.created_at
        FROM device_history AS dh
        WHERE 1 = 1
    ";

    // Parameter query
    $params = [];

    // Filter tanggal mulai
    if (!empty($start_date)) {
        $sql .= " AND DATE(dh.created_at) >= ?";
        $params[] = $start_date;
    }

    // Filter tanggal akhir
    if (!empty($end_date)) {
        $sql .= " AND DATE(dh.created_at) <= ?";
        $params[] = $end_date;
    }

    // Data terbaru ditampilkan paling atas
    $sql .= "
        ORDER BY
            dh.created_at DESC,
            dh.id DESC
    ";

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
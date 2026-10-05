<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

    // Logika: Tampilkan jika device masih bermasalah (offline/warning) 
    // ATAU jika status perbaikannya sudah diubah menjadi 'Sudah diperbaiki' (biar tidak hilang)
    $sql = "
        SELECT
            rh.id,
            rh.location,
            rh.device_name,
            rh.device_ip,
            rh.device_type,
            rh.status AS status,
            rh.notes,
            rh.repair_notes,
            rh.image,
            rh.image_before,
            rh.image_after,
            rh.created_at,
            rh.updated_at
        FROM repair_history rh
        JOIN monitoring_logs ml 
          ON rh.location COLLATE utf8mb4_unicode_ci = ml.location COLLATE utf8mb4_unicode_ci 
         AND rh.device_ip COLLATE utf8mb4_unicode_ci = ml.device_ip COLLATE utf8mb4_unicode_ci
        JOIN (
            SELECT location, device_ip, MAX(id) as max_id
            FROM repair_history
            GROUP BY location, device_ip
        ) latest ON rh.id = latest.max_id
        WHERE (ml.status != 'ONLINE' AND ml.status != 'UP') 
           OR rh.status = 'Sudah diperbaiki'
    ";

    $params = [];

    if (!empty($start_date)) {
        $sql .= " AND DATE(rh.created_at) >= ?";
        $params[] = $start_date;
    }

    if (!empty($end_date)) {
        $sql .= " AND DATE(rh.created_at) <= ?";
        $params[] = $end_date;
    }

    $sql .= " ORDER BY rh.updated_at DESC, rh.id DESC";

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
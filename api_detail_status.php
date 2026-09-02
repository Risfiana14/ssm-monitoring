<?php
// api_detail_status.php
header('Content-Type: application/json');
require_once 'db.php';

$trainset = $_GET['trainset'] ?? 'Argo Wilis';

try {
    // Query Sederhana & Aman tanpa subquery rumit yang menyebabkan crash
    $query = "
        SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image
        FROM monitoring_logs
        WHERE trainset = ?
        ORDER BY id ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$trainset]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter Ambil Log Terakhir per Gerbong & per Perangkat di PHP (100% Bebas Error SQL)
    $latestDevices = [];
    foreach ($rows as $row) {
        $key = $row['location'] . '_' . $row['device_name'];
        $latestDevices[$key] = $row;
    }

    // Urutkan Nama Perangkat Sesuai Urutan 1-16
    $orderedNames = [
        'NVR', 'CCTV 1', 'CCTV 2', 'INDOOR 1', 'INDOOR 2', 
        'OUTDOOR 1', 'OUTDOOR 2', 'SOT TV 1', 'SOT TV 2', 
        'MINI PC', 'SWITCH', 'ROUTER', 'MODEM', 'WIFI', 
        'PLCVCU', 'CCTV 3'
    ];

    $result = array_values($latestDevices);

    usort($result, function($a, $b) use ($orderedNames) {
        // 1. Urutkan berdasar Gerbong
        if ($a['location'] !== $b['location']) {
            return strcmp($a['location'], $b['location']);
        }
        // 2. Urutkan berdasar Nama Perangkat
        $posA = array_search($a['device_name'], $orderedNames);
        $posB = array_search($b['device_name'], $orderedNames);
        
        $posA = ($posA === false) ? 99 : $posA;
        $posB = ($posB === false) ? 99 : $posB;
        
        return $posA - $posB;
    });

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
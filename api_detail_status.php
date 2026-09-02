<?php
// api_detail_status.php
header('Content-Type: application/json');
require_once 'db.php';

$trainset = $_GET['trainset'] ?? 'Argo Wilis';

try {
    $query = "
        SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image, upload_count, image_updated_at, notes
        FROM monitoring_logs
        WHERE trainset = ?
        ORDER BY id ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$trainset]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $latestDevices = [];
    foreach ($rows as $row) {
        $key = $row['location'] . '_' . $row['device_name'];
        
        if (isset($latestDevices[$key])) {
            if (empty($row['notes']) || trim($row['notes']) === '') {
                $row['notes'] = $latestDevices[$key]['notes'] ?? null;
            }
            
            if (empty($row['image']) || trim($row['image']) === '') {
                $row['image'] = $latestDevices[$key]['image'] ?? null;
                $row['upload_count'] = $latestDevices[$key]['upload_count'] ?? 0;
                $row['image_updated_at'] = $latestDevices[$key]['image_updated_at'] ?? null;
            }
        }

        $latestDevices[$key] = $row;
    }

    $orderedNames = [
        'NVR', 'CCTV 1', 'CCTV 2', 'INDOOR 1', 'INDOOR 2', 
        'OUTDOOR 1', 'OUTDOOR 2', 'SOT TV 1', 'SOT TV 2', 
        'MINI PC', 'SWITCH', 'ROUTER', 'MODEM', 'WIFI', 
        'PLCVCU', 'CCTV 3'
    ];

    $result = array_values($latestDevices);

    usort($result, function($a, $b) use ($orderedNames) {
        if ($a['location'] !== $b['location']) {
            return strcmp($a['location'], $b['location']);
        }
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
?>
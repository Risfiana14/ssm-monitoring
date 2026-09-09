<?php

// Inisialisasi API & Database
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta'); // Memastikan jam server cocok dengan WIB

require_once 'db.php';

$trainset = $_GET['trainset'] ?? 'DAOP_8';

try {

    if ($trainset === 'DAOP_8' || $trainset === 'ALL' || empty($trainset)) {
        $query = "
            SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image, upload_count, image_updated_at, notes
            FROM monitoring_logs
            ORDER BY id ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    } else {
        $query = "
            SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image, upload_count, image_updated_at, notes
            FROM monitoring_logs
            WHERE trainset = ?
            ORDER BY id ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$trainset]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapping Unik Perangkat per Gerbong (Location ID)
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

    // -------------------------------------------------------------------------
    // LOGIKA KHUSUS INTERNET STATUS (BERDASARKAN LAST UPDATE DATA)
    // -------------------------------------------------------------------------
    $currentTime = time();
    $toleransiDetik = 1 * 60; // GANTI DISINI: 1 Menit (60 detik). Kalau mau 5 menit: 5 * 60

    foreach ($result as &$device) {
        // Ambil string timestamp dari DB
        $lastUpdateString = !empty($device['image_updated_at']) ? $device['image_updated_at'] : ($device['timestamp'] ?? null);
        
        // Parse timestamp ke UNIX Seconds
        $lastUpdateUnix = $lastUpdateString ? strtotime($lastUpdateString) : 0;
        
        // Hitung selisih detik
        $selisihDetik = $currentTime - $lastUpdateUnix;

        // KALAU KURANG DARI TOLERANSI (MASIH RUTIN UPDATE) = INTERNET
        // KALAU LEBIH DARI TOLERANSI ATAU TIDAK ADA DATA = NO INTERNET
        if ($lastUpdateUnix > 0 && $selisihDetik <= $toleransiDetik) {
            $device['internet_status'] = 'INTERNET';
        } else {
            $device['internet_status'] = 'NO INTERNET';
        }

        // BISA DIGUNAKAN UNTUK DEBUGGING DI CONSOLE
        $device['selisih_detik'] = $selisihDetik;
    }
    unset($device);

    // Sorting Data Berdasarkan Gerbong & Nama Perangkat
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
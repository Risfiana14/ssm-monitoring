<?php
// ssm_receive.php
require_once 'db.php';

$deviceName   = $_GET['device_name'] ?? null;
$deviceIP     = $_GET['device_ip'] ?? null;
$deviceType   = $_GET['device_type'] ?? null;
$trainset     = $_GET['trainset'] ?? null;
$locationCode = $_GET['location_code'] ?? null;
$gerbongID    = $_GET['gerbong_id'] ?? null;
$status       = strtoupper($_GET['status'] ?? 'OFFLINE');

if ($deviceIP && $locationCode) {
    // 1. Cek dulu status terakhir perangkat ini di database
    $checkStmt = $pdo->prepare("SELECT status FROM monitoring_logs WHERE device_ip = ? AND location = ? LIMIT 1");
    $checkStmt->execute([$deviceIP, $locationCode]);
    $lastData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // 2. Jika datanya sudah ada dan statusnya SAMA PERSIS dengan yang dikirim, 
    //    jangan lakukan apa-apa (abaikan, tidak perlu update database).
    if ($lastData && $lastData['status'] === $status) {
        echo "NO_CHANGE";
        exit;
    }

    // 3. Jika statusnya BERUBAH (atau data belum ada), baru simpan/perbarui ke database
    $stmt = $pdo->prepare("
        INSERT INTO monitoring_logs (device_name, device_ip, device_type, trainset, location, gerbong_id, status, timestamp) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            status = VALUES(status),
            timestamp = NOW(),
            device_name = VALUES(device_name),
            device_type = VALUES(device_type),
            trainset = VALUES(trainset),
            gerbong_id = VALUES(gerbong_id)
    ");
    
    $stmt->execute([$deviceName, $deviceIP, $deviceType, $trainset, $locationCode, $gerbongID, $status]);
    echo "UPDATED_OK";
} else {
    http_response_code(400);
    echo "Bad Request";
}
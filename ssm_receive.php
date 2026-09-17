<?php

// Endpoint Penerima Data Netwatch MikroTik
require_once 'db.php';

$deviceName   = $_GET['device_name'] ?? null;
$deviceIP     = $_GET['device_ip'] ?? null;
$deviceType   = $_GET['device_type'] ?? null;
$trainset     = $_GET['trainset'] ?? null;
$locationCode = $_GET['location_code'] ?? null; 
$status       = strtoupper($_GET['status'] ?? 'OFFLINE');
$internetStatus = strtoupper($_GET['internet_status'] ?? '');

// =========================================================================
// AUTO-REGISTER KERETA
// =========================================================================
if ($locationCode) {
    $regStmt = $pdo->prepare("
        INSERT INTO carriages (location_code)
        VALUES (?)
        ON DUPLICATE KEY UPDATE location_code = VALUES(location_code)
    ");
    $regStmt->execute([$locationCode]);
}

// =========================================================================
// PERBAIKAN: Update internet_status di carriages SEKALIGUS perbarui timestamp router
// =========================================================================
if ($locationCode && $internetStatus) {
    $stmt = $pdo->prepare("
        INSERT INTO carriages (location_code, internet_status) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE internet_status = VALUES(internet_status)
    ");
    $stmt->execute([$locationCode, $internetStatus]);

    // Memperbarui monitoring_logs untuk router agar timestamp-nya tetap berjalan (detak jantung)
    $logStmt = $pdo->prepare("
        INSERT INTO monitoring_logs (device_name, device_ip, device_type, location, status, timestamp) 
        VALUES ('ROUTER', '192.168.10.254', 'router', ?, 'ONLINE', NOW())
        ON DUPLICATE KEY UPDATE timestamp = NOW(), status = 'ONLINE'
    ");
    $logStmt->execute([$locationCode]);

    echo "CARRIAGE_INTERNET_UPDATED_" . $internetStatus;
    exit;
}

// Validasi nilai status perangkat
if (!in_array($status, ['ONLINE', 'OFFLINE'])) {
    $status = 'OFFLINE';
}

if ($deviceIP && $locationCode) {
    $checkStmt = $pdo->prepare("SELECT status FROM monitoring_logs WHERE device_ip = ? AND location = ? ORDER BY timestamp DESC LIMIT 1");
    $checkStmt->execute([$deviceIP, $locationCode]);
    $lastData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($lastData && $lastData['status'] === $status) {
        echo "NO_CHANGE";
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO monitoring_logs (device_name, device_ip, device_type, trainset, location, status, timestamp) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            status = VALUES(status),
            timestamp = NOW(),
            device_name = VALUES(device_name),
            device_type = VALUES(device_type),
            trainset = VALUES(trainset)
    ");
    
    $stmt->execute([$deviceName, $deviceIP, $deviceType, $trainset, $locationCode, $status]);
    echo "UPDATED_OK";
} else {
    http_response_code(400);
    echo "Bad Request";
}
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
// PERBAIKAN: Update internet_status pada baris data gerbong yang sudah ada 
// TANPA membuat baris baru/kotak perangkat baru di database.
// =========================================================================
if ($locationCode && $internetStatus) {
    $stmt = $pdo->prepare("
        UPDATE monitoring_logs 
        SET internet_status = ? 
        WHERE location = ?
    ");
    $stmt->execute([$internetStatus, $locationCode]);
    echo "INTERNET_STATUS_UPDATED";
    exit;
}
// =========================================================================

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
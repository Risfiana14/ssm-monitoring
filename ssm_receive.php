<?php

// Endpoint Penerima Data Netwatch MikroTik
require_once 'db.php';

$deviceName   = $_GET['device_name'] ?? null;
$deviceIP     = $_GET['device_ip'] ?? null;
$deviceType   = $_GET['device_type'] ?? null;
$trainset     = $_GET['trainset'] ?? null;
$locationCode = $_GET['location'] ?? null; 
$status       = strtoupper($_GET['status'] ?? 'OFFLINE');
$internetStatus = strtoupper($_GET['internet_status'] ?? '');

// =========================================================================
// AUTO-REGISTER KERETA & UPDATE STATUS INTERNET
// =========================================================================
if ($locationCode) {
    // 1. Pastikan gerbong terdaftar di tabel carriages
    $regStmt = $pdo->prepare("
        INSERT INTO carriages (location)
        VALUES (?)
        ON DUPLICATE KEY UPDATE location = VALUES(location)
    ");
    $regStmt->execute([$locationCode]);

    // 2. Jika parameter internet_status dikirim, perbarui status internetnya
    if ($internetStatus) {
        $internetStmt = $pdo->prepare("
            UPDATE carriages 
            SET internet_status = ? 
            WHERE location = ?
        ");
        $internetStmt->execute([$internetStatus, $locationCode]);

        // Memperbarui monitoring_logs untuk router agar timestamp-nya tetap berjalan (detak jantung)
        $logStmt = $pdo->prepare("
            INSERT INTO monitoring_logs (device_name, device_ip, device_type, location, status, timestamp) 
            VALUES ('ROUTER', '192.168.10.254', 'router', ?, 'ONLINE', NOW())
            ON DUPLICATE KEY UPDATE timestamp = NOW(), status = 'ONLINE'
        ");
        $logStmt->execute([$locationCode]);
    }
}

// Validasi nilai status perangkat
if (!in_array($status, ['ONLINE', 'OFFLINE'])) {
    $status = 'OFFLINE';
}

// =========================================================================
// PENYIMPANAN / UPDATE DATA PERANGKAT MONITORING
// =========================================================================
if ($deviceIP && $locationCode) {
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
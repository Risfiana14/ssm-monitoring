<?php

// Endpoint Penerima Data Netwatch MikroTik
require_once 'db.php';

// Menangkap parameter dinamis yang dikirimkan oleh router MikroTik
$deviceName   = $_GET['device_name'] ?? null;
$deviceIP     = $_GET['device_ip'] ?? null;
$deviceType   = $_GET['device_type'] ?? null;
$trainset     = $_GET['trainset'] ?? null;
$locationCode = $_GET['location_code'] ?? null; // Berperan sebagai ID Gerbong / Location ID
$status       = strtoupper($_GET['status'] ?? 'OFFLINE');

if ($deviceIP && $locationCode) {

    // Cek Status Terakhir (Pencegahan Database Bloat / Spam Request)

    $checkStmt = $pdo->prepare("SELECT status FROM monitoring_logs WHERE device_ip = ? AND location = ? LIMIT 1");
    $checkStmt->execute([$deviceIP, $locationCode]);
    $lastData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Jika status sama persis dengan database, abaikan proses (tidak update)
    if ($lastData && $lastData['status'] === $status) {
        echo "NO_CHANGE";
        exit;
    }
    
    // Upsert Database Berdasarkan IP dan Lokasi Gerbong

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
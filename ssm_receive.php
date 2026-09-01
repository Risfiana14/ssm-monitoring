<?php
// ssm_receive.php
require_once 'db.php';

$deviceName   = $_GET['device_name'] ?? null;
$deviceIP     = $_GET['device_ip'] ?? null;
$deviceType   = $_GET['device_type'] ?? null;
$trainset     = $_GET['trainset'] ?? null;
$locationCode = $_GET['location_code'] ?? null; // e.g. MC1, M1, T1, T2, M2, MC2
$status       = $_GET['status'] ?? 'offline';

if ($deviceIP && $locationCode) {
    $stmt = $pdo->prepare("INSERT INTO monitoring_logs (device_name, device_ip, device_type, trainset, location, status, timestamp) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$deviceName, $deviceIP, $deviceType, $trainset, $locationCode, strtoupper($status)]);
    echo "OK";
} else {
    http_response_code(400);
    echo "Bad Request";
}
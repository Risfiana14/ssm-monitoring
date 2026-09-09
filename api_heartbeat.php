<?php
// api_heartbeat.php
require_once 'db.php';
date_default_timezone_set('Asia/Jakarta');

$location = $_GET['location'] ?? '';

if (!empty($location)) {
    // Memperbarui timestamp gerbong ke waktu detik ini
    $stmt = $pdo->prepare("
        UPDATE monitoring_logs 
        SET timestamp = NOW() 
        WHERE location = ?
    ");
    $stmt->execute([$location]);
    echo "OK";
}
?>
<?php
// api_heartbeat.php
require_once 'db.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil parameter dari GET (dukung location maupun location_code)
$location = $_GET['location'] ?? $_GET['location_code'] ?? '';

if (!empty($location)) {
    // Update timestamp SEMUA baris perangkat di gerbong tersebut ke detik ini
    $stmt = $pdo->prepare("
        UPDATE monitoring_logs 
        SET timestamp = NOW(), image_updated_at = NOW() 
        WHERE location = ?
    ");
    $stmt->execute([$location]);
    echo "OK - Heartbeat Updated for " . htmlspecialchars($location);
} else {
    echo "ERROR - Location parameter missing";
}
?>
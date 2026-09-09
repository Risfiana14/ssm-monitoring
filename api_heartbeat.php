<?php
// api_heartbeat.php
require_once 'db.php';
date_default_timezone_set('Asia/Jakarta');

$location = trim($_GET['location'] ?? $_GET['location_code'] ?? '');

if (!empty($location)) {
    $stmt = $pdo->prepare("
        UPDATE monitoring_logs 
        SET timestamp = NOW(), image_updated_at = NOW() 
        WHERE TRIM(location) = ?
    ");
    $stmt->execute([$location]);
    echo "OK";
} else {
    echo "ERROR: Location parameter missing";
}
?>
<?php
require_once 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT c.location_code, c.internet_status, MAX(m.timestamp) as last_timestamp
    FROM carriages c
    LEFT JOIN monitoring_logs m ON c.location_code = m.location AND m.device_type = 'router'
    GROUP BY c.location_code, c.internet_status
    ORDER BY c.location_code ASC
");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cars);
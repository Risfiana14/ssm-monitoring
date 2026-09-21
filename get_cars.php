<?php
require_once 'db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT 
        c.location, 
        CASE 
            WHEN MAX(m.timestamp) IS NULL THEN 'NO_INTERNET'
            WHEN TIMESTAMPDIFF(SECOND, MAX(m.timestamp), NOW()) > 120 THEN 'NO_INTERNET'
            ELSE 'INTERNET'
        END as internet_status,
        MAX(m.timestamp) as last_timestamp
    FROM carriages c
    LEFT JOIN monitoring_logs m ON c.location = m.location AND m.device_type = 'router'
    GROUP BY c.location
    ORDER BY c.location ASC
");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cars);
?>
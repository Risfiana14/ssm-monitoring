<?php

require_once 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT
        c.location,
        c.is_hidden,
        c.train_id,
        c.carriage_order,
        CASE
            WHEN r.last_timestamp IS NULL THEN 'NO_INTERNET'
            WHEN TIMESTAMPDIFF(SECOND, r.last_timestamp, NOW()) > 120 THEN 'NO_INTERNET'
            ELSE 'INTERNET'
        END AS internet_status,
        r.last_timestamp
    FROM carriages c
    LEFT JOIN (
        SELECT
            location,
            MAX(timestamp) AS last_timestamp
        FROM monitoring_logs
        WHERE device_type = 'router'
        GROUP BY location
    ) r ON c.location = r.location
    WHERE c.is_hidden = 0
    ORDER BY c.carriage_order ASC, c.location ASC
");

$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cars);
?>
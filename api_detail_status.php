<?php
// api_detail_status.php
header('Content-Type: application/json');
require_once 'db.php';

$trainset = $_GET['trainset'] ?? 'Argo Wilis';

$query = "
    SELECT l1.device_name, l1.device_ip, l1.device_type, l1.trainset, l1.location, l1.status, l1.timestamp
    FROM monitoring_logs l1
    INNER JOIN (
        SELECT device_ip, MAX(id) as max_id
        FROM monitoring_logs
        WHERE trainset = ?
        GROUP BY device_ip
    ) l2 ON l1.id = l2.max_id
    ORDER BY l1.location ASC, INET_ATON(l1.device_ip) ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute([$trainset]);
$devices = $stmt->fetchAll();

echo json_encode($devices);
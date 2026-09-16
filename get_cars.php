<?php

// Endpoint: mengambil daftar kereta beserta status internetnya dari tabel carriages.
// Dipakai oleh index.php untuk merender dashboard.
require_once 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT location_code, internet_status
    FROM carriages
    ORDER BY location_code ASC
");

// Mengambil seluruh data sebagai associative array (berisi location_code dan internet_status)
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cars);
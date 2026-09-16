<?php

// Endpoint: mengambil daftar kereta yang AKTIF (belum dihapus) dari tabel carriages.
// Dipakai oleh index.php untuk menggantikan array uniqueCars yang dulu hardcode.
require_once 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("
    SELECT location_code
    FROM carriages
    ORDER BY location_code ASC
");

$cars = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($cars);
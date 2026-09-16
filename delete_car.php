<?php

// Endpoint: menghapus kereta dari dashboard.
// Ini HANYA menghapus baris di tabel `carriages` (daftar tampilan),
// TIDAK menghapus data historis di `monitoring_logs`.
// Kalau kereta ini mengirim data lagi lewat ssm_receive.php, dia akan
// otomatis terdaftar lagi di carriages (lihat auto-register di ssm_receive.php).
require_once 'db.php';

header('Content-Type: application/json');

$locationCode = $_POST['location_code'] ?? null;

if (!$locationCode) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'location_code wajib diisi']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM carriages WHERE location_code = ?");
$stmt->execute([$locationCode]);

echo json_encode([
    'status'  => 'ok',
    'message' => "$locationCode dihapus dari dashboard"
]);
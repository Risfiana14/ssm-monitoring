<?php

require_once 'db.php';

header('Content-Type: application/json');

$location = trim($_POST['location'] ?? '');

if ($location === '') {
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'location wajib diisi'
    ]);
    exit;
}

try {

    // Sembunyikan nomor sarana dari dashboard utama.
    // Data carriages tetap disimpan.
    $stmt = $pdo->prepare("
        UPDATE carriages
        SET is_hidden = 1
        WHERE location = ?
    ");
    $stmt->execute([$location]);

    // Hapus heartbeat router lama.
    // Router yang masih mati tidak boleh dianggap sebagai router yang baru aktif.
    $stmtRouter = $pdo->prepare("
        DELETE FROM monitoring_logs
        WHERE location = ?
          AND device_type = 'router'
    ");
    $stmtRouter->execute([$location]);

    echo json_encode([
        'status' => 'ok',
        'message' => "$location berhasil disembunyikan dari dashboard"
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyembunyikan nomor sarana'
    ]);
}
?>
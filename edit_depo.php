<?php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard_main.php');
    exit;
}

$depo_id = (int)($_POST['depo_id'] ?? 0);
$nama_depo = trim($_POST['nama_depo'] ?? '');

if ($depo_id <= 0 || $nama_depo === '') {
    exit('Data depo tidak valid.');
}

try {

    $stmt = $pdo->prepare("
        UPDATE depos
        SET nama_depo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nama_depo,
        $depo_id
    ]);

    header('Location: dashboard_main.php?status=sukses_edit_depo');
    exit;

} catch (PDOException $e) {

    exit(
        'Gagal mengubah nama depo: ' .
        htmlspecialchars($e->getMessage())
    );
}
?>
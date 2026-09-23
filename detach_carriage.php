<?php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard_main.php');
    exit;
}

$location = trim($_POST['location'] ?? '');
$train_id = (int)($_POST['train_id'] ?? 0);

if ($location === '') {
    header('Location: dashboard_main.php');
    exit;
}

try {

    $stmt = $pdo->prepare("
        UPDATE carriages
        SET train_id = NULL
        WHERE location = ?
    ");

    $stmt->execute([$location]);

    // Kembali ke rangkaian yang sedang dibuka
    if ($train_id > 0) {
        header('Location: dashboard_main.php?train_id=' . $train_id);
    } else {
        header('Location: dashboard_main.php');
    }

    exit;

} catch (PDOException $e) {

    echo 'Gagal melepas nomor sarana: ' . htmlspecialchars($e->getMessage());

}
?>
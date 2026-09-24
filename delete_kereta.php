<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard_main.php');
    exit;
}

$train_id = (int)($_POST['train_id'] ?? 0);

if ($train_id <= 0) {
    exit('ID kereta tidak valid.');
}

try {
    $pdo->beginTransaction();

    // Lepaskan semua nomor sarana dari rangkaian ini
    $stmtDetach = $pdo->prepare("
        UPDATE carriages
        SET train_id = NULL
        WHERE train_id = ?
    ");
    $stmtDetach->execute([$train_id]);

    // Hapus nama rangkaian/kereta
    $stmtDelete = $pdo->prepare("
        DELETE FROM trains
        WHERE id = ?
    ");
    $stmtDelete->execute([$train_id]);

    $pdo->commit();

    header("Location: dashboard_main.php?status=sukses_hapus_kereta");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit('Gagal menghapus nama kereta: ' . $e->getMessage());
}
?>
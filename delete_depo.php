<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard_main.php');
    exit;
}

$depo_id = (int)($_POST['depo_id'] ?? 0);

if ($depo_id <= 0) {
    exit('ID depo tidak valid.');
}

try {
    $pdo->beginTransaction();

    // Ambil semua train yang berada di depo ini
    $stmtTrain = $pdo->prepare("
        SELECT id
        FROM trains
        WHERE depo_id = ?
    ");
    $stmtTrain->execute([$depo_id]);

    $trainIds = $stmtTrain->fetchAll(PDO::FETCH_COLUMN);

    // Lepaskan nomor sarana dari semua rangkaian
    if (!empty($trainIds)) {
        $stmtDetach = $pdo->prepare("
            UPDATE carriages
            SET train_id = NULL
            WHERE train_id = ?
        ");

        foreach ($trainIds as $trainId) {
            $stmtDetach->execute([(int)$trainId]);
        }

        // Hapus semua nama kereta di depo tersebut
        $stmtDeleteTrain = $pdo->prepare("
            DELETE FROM trains
            WHERE depo_id = ?
        ");
        $stmtDeleteTrain->execute([$depo_id]);
    }

    // Hapus depo
    $stmtDeleteDepo = $pdo->prepare("
        DELETE FROM depos
        WHERE id = ?
    ");
    $stmtDeleteDepo->execute([$depo_id]);

    $pdo->commit();

    header("Location: dashboard_main.php?status=sukses_hapus_depo");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit('Gagal menghapus depo: ' . $e->getMessage());
}
?>
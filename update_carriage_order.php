<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainId = $_POST['train_id'] ?? 0;
    $orders = $_POST['order'] ?? []; // Array berisi [car_id => new_order]

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE carriages SET carriage_order = ? WHERE id = ?");
        
        foreach ($orders as $carriageId => $newOrder) {
            $stmt->execute([(int)$newOrder, (int)$carriageId]);
        }

        $pdo->commit();
        header("Location: dashboard_main.php?train_id=" . $trainId . "&status=success_order");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: dashboard_main.php?train_id=" . $trainId . "&status=error_order");
        exit;
    }
}
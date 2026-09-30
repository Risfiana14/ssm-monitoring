<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $field = $_POST['field'] ?? null;
    $value = $_POST['value'] ?? '';

    // Izinkan kolom status dan repair_notes untuk diupdate
    $allowedFields = ['status', 'repair_notes'];

    if (!$id || !in_array($field, $allowedFields)) {
        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE repair_history
            SET {$field} = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$value, $id]);

        echo json_encode(['status' => 'ok', 'message' => 'Berhasil diperbarui.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>
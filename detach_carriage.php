<?php

require_once 'db.php';

header('Content-Type: application/json');

$location = trim($_POST['location'] ?? '');

if ($location === '') {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Nomor sarana tidak ditemukan.'
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare("
        UPDATE carriages
        SET train_id = NULL
        WHERE location = ?
    ");

    $stmt->execute([$location]);

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            'status' => 'ok',
            'message' => $location . ' berhasil dilepas dari rangkaian.'
        ]);

    } else {

        echo json_encode([
            'status' => 'error',
            'message' => $location . ' tidak ditemukan atau sudah tidak berada dalam rangkaian.'
        ]);
    }

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
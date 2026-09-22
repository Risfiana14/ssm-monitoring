<?php

require_once 'db.php';

header('Content-Type: application/json');

$train_id = trim($_POST['train_id'] ?? '');
$location = trim($_POST['location'] ?? '');

if ($train_id === '' || $location === '') {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Rangkaian dan nomor sarana wajib dipilih.'
    ]);

    exit;
}

try {

    // 1. Pastikan rangkaian memang ada
    $stmtTrain = $pdo->prepare("
        SELECT id, nama_kereta
        FROM trains
        WHERE id = ?
        LIMIT 1
    ");

    $stmtTrain->execute([$train_id]);

    $train = $stmtTrain->fetch(PDO::FETCH_ASSOC);

    if (!$train) {

        http_response_code(404);

        echo json_encode([
            'status' => 'error',
            'message' => 'Rangkaian kereta tidak ditemukan.'
        ]);

        exit;
    }

    // 2. Pastikan nomor sarana memang ada di monitoring
    $stmtMonitoring = $pdo->prepare("
        SELECT 1
        FROM monitoring_logs
        WHERE location = ?
        LIMIT 1
    ");

    $stmtMonitoring->execute([$location]);

    if (!$stmtMonitoring->fetchColumn()) {

        http_response_code(404);

        echo json_encode([
            'status' => 'error',
            'message' => 'Nomor sarana tidak ditemukan pada data monitoring.'
        ]);

        exit;
    }

    // 3. Cari data carriage
    $stmtCarriage = $pdo->prepare("
        SELECT id, train_id
        FROM carriages
        WHERE location = ?
        LIMIT 1
    ");

    $stmtCarriage->execute([$location]);

    $carriage = $stmtCarriage->fetch(PDO::FETCH_ASSOC);

    // 4. Jika belum ada di carriages, buat
    if (!$carriage) {

        $insert = $pdo->prepare("
            INSERT INTO carriages (location, train_id)
            VALUES (?, ?)
        ");

        $insert->execute([
            $location,
            $train_id
        ]);

    } else {

        // 5. Kalau sudah dipakai rangkaian lain
        if (
            $carriage['train_id'] !== null &&
            (int)$carriage['train_id'] !== (int)$train_id
        ) {

            http_response_code(409);

            echo json_encode([
                'status' => 'error',
                'message' => 'Nomor sarana sedang digunakan oleh rangkaian lain.'
            ]);

            exit;
        }

        // 6. Assign ke rangkaian yang dipilih
        $update = $pdo->prepare("
            UPDATE carriages
            SET train_id = ?
            WHERE location = ?
        ");

        $update->execute([
            $train_id,
            $location
        ]);
    }

    echo json_encode([
        'status' => 'ok',
        'message' => $location .
                     ' berhasil ditambahkan ke rangkaian ' .
                     $train['nama_kereta']
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan nomor sarana.'
    ]);
}
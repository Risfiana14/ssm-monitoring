<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak diizinkan.'
    ]);

    exit;
}

$id = (int)($_POST['id'] ?? 0);
$field = $_POST['field'] ?? '';

$allowedFields = ['image_before', 'image_after'];

if ($id <= 0 || !in_array($field, $allowedFields, true)) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter tidak valid.'
    ]);

    exit;
}

if (
    !isset($_FILES['image']) ||
    $_FILES['image']['error'] !== UPLOAD_ERR_OK
) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'File gambar tidak ditemukan.'
    ]);

    exit;
}

$file = $_FILES['image'];

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

$extension = strtolower(
    pathinfo($file['name'], PATHINFO_EXTENSION)
);

if (!in_array($extension, $allowedExtensions, true)) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => 'Format gambar tidak didukung.'
    ]);

    exit;
}

try {

    // =========================================================
    // 1. AMBIL DATA REPAIR
    // =========================================================
    $stmtCheck = $pdo->prepare("
        SELECT
            id,
            location,
            device_ip,
            status
        FROM repair_history
        WHERE id = ?
        LIMIT 1
    ");

    $stmtCheck->execute([$id]);

    $repair = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$repair) {

        http_response_code(404);

        echo json_encode([
            'status' => 'error',
            'message' => 'Data laporan perbaikan tidak ditemukan.'
        ]);

        exit;
    }


    // =========================================================
    // 2. FOLDER UPLOAD
    // =========================================================
    $uploadDir = __DIR__ . '/uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }


    // =========================================================
    // 3. NAMA FILE UNIK
    // =========================================================
    $fileName =
        'repair_' .
        $id . '_' .
        $field . '_' .
        time() . '_' .
        bin2hex(random_bytes(4)) .
        '.' .
        $extension;

    $targetPath = $uploadDir . $fileName;


    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {

        throw new Exception(
            'Gagal menyimpan file gambar.'
        );
    }


    // =========================================================
    // 4. SIMPAN KE REPAIR_HISTORY
    // =========================================================
    $sql = "
        UPDATE repair_history
        SET
            {$field} = ?,
            updated_at = NOW()
        WHERE id = ?
    ";

    $stmtUpdate = $pdo->prepare($sql);

    $stmtUpdate->execute([
        $fileName,
        $id
    ]);


    // =========================================================
    // 5. JIKA GAMBAR ADALAH IMAGE_AFTER
    //    DAN STATUS SUDAH DIPERBAIKI
    //    → SINKRONKAN KE MONITORING_LOGS
    // =========================================================
    if (
        $field === 'image_after' &&
        $repair['status'] === 'Sudah diperbaiki'
    ) {

        $stmtMonitoring = $pdo->prepare("
            UPDATE monitoring_logs
            SET
                image = ?,
                image_updated_at = NOW()
            WHERE location = ?
            AND device_ip = ?
        ");

        $stmtMonitoring->execute([
            $fileName,
            $repair['location'],
            $repair['device_ip']
        ]);
    }


    echo json_encode([
        'status' => 'ok',
        'message' => 'Gambar berhasil disimpan.',
        'file' => $fileName,
        'field' => $field
    ], JSON_UNESCAPED_UNICODE);


} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>
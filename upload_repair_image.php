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
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Format gambar tidak didukung.'
    ]);
    exit;
}

try {

    // Pastikan data laporan perbaikan memang ada
    $stmtCheck = $pdo->prepare("
        SELECT id
        FROM repair_history
        WHERE id = ?
        LIMIT 1
    ");

    $stmtCheck->execute([$id]);

    if (!$stmtCheck->fetchColumn()) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Data laporan perbaikan tidak ditemukan.'
        ]);
        exit;
    }

    // Folder upload
    $uploadDir = __DIR__ . '/uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Nama file unik
    $fileName = 'repair_' . $id . '_' . $field . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Gagal menyimpan file gambar.');
    }

    // Simpan nama file ke kolom yang sesuai
    $sql = "
        UPDATE repair_history
        SET {$field} = ?,
            updated_at = NOW()
        WHERE id = ?
    ";

    $stmtUpdate = $pdo->prepare($sql);

    $stmtUpdate->execute([
        $fileName,
        $id
    ]);

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
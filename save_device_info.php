<?php
// save_device_info.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceIP = $_POST['device_ip'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$deviceIP) {
        die("Device IP tidak valid.");
    }

    // 1. Update Catatan / Tindak Lanjut
    $stmt = $pdo->prepare("UPDATE monitoring_logs SET notes = ? WHERE device_ip = ?");
    $stmt->execute([$notes, $deviceIP]);

    // 2. Jika Ada File Foto yang Diunggah
    if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['device_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $newFileName = 'dev_' . md5($deviceIP . time()) . '.' . $ext;
            $targetPath = 'uploads/' . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmtImg = $pdo->prepare("UPDATE monitoring_logs SET image = ? WHERE device_ip = ?");
                $stmtImg->execute([$newFileName, $deviceIP]);
            }
        }
    }

    // Kembali ke halaman dashboard utama
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
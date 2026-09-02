<?php
// upload_image.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['device_image'])) {
    $deviceIP = $_POST['device_ip'] ?? null;
    $file = $_FILES['device_image'];

    if (!$deviceIP || $file['error'] !== UPLOAD_ERR_OK) {
        die("Gagal mengunggah file.");
    }

    // 1. Cek jumlah upload saat ini untuk IP Perangkat tersebut
    $stmtCheck = $pdo->prepare("SELECT upload_count FROM monitoring_logs WHERE device_ip = ? LIMIT 1");
    $stmtCheck->execute([$deviceIP]);
    $deviceData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    $currentCount = $deviceData['upload_count'] ?? 0;

    // Batasi maksimal 4 kali upload
    if ($currentCount >= 4) {
        die("<script>alert('Batas maksimal upload foto (4 kali) untuk perangkat ini telah tercapai!'); window.history.back();</script>");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Format file harus JPG, JPEG, PNG, atau WEBP.");
    }

    // Pastikan folder uploads ada
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = 'dev_' . md5($deviceIP . time()) . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // 2. Update foto, tambah 1 hitungan upload (+1), dan perbarui timestamp foto terakhir
        $stmtUpdate = $pdo->prepare("
            UPDATE monitoring_logs 
            SET image = ?, 
                upload_count = upload_count + 1, 
                image_updated_at = NOW() 
            WHERE device_ip = ?
        ");
        $stmtUpdate->execute([$newFileName, $deviceIP]);

        // Redirect kembali ke halaman utama
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header("Location: " . $referer);
        exit;
    } else {
        echo "Gagal menyimpan file ke folder uploads.";
    }
}
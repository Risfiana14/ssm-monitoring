<?php
// save_device_info.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceIP = $_POST['device_ip'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$deviceIP) {
        die("Device IP tidak valid.");
    }

    // 1. Simpan/Update Catatan dari Teman
    $stmtNotes = $pdo->prepare("UPDATE monitoring_logs SET notes = ? WHERE device_ip = ?");
    $stmtNotes->execute([$notes, $deviceIP]);

    // 2. Jika Ada File Foto yang Diunggah (Proses Fitur Upload Kamu)
    if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] === UPLOAD_ERR_OK) {
        
        // Cek Batas Upload (Maksimal 4 Kali)
        $stmtCheck = $pdo->prepare("SELECT upload_count FROM monitoring_logs WHERE device_ip = ? LIMIT 1");
        $stmtCheck->execute([$deviceIP]);
        $deviceData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $currentCount = $deviceData['upload_count'] ?? 0;

        if ($currentCount >= 4) {
            die("<script>alert('Batas maksimal upload foto (4 kali) untuk perangkat ini telah tercapai!'); window.history.back();</script>");
        }

        $file = $_FILES['device_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            // Buat folder uploads jika belum ada
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'dev_' . md5($deviceIP . time()) . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update Foto, Tambah 1 Hitungan Upload (+1), dan Update Timestamp Foto Terakhir
                $stmtImg = $pdo->prepare("
                    UPDATE monitoring_logs 
                    SET image = ?, 
                        upload_count = upload_count + 1, 
                        image_updated_at = NOW() 
                    WHERE device_ip = ?
                ");
                $stmtImg->execute([$newFileName, $deviceIP]);
            }
        }
    }

    // Kembali ke Halaman Utama
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $referer);
    exit;
}
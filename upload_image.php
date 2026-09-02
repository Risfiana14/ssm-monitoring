<?php
// upload_image.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['device_image'])) {
    $deviceIP = $_POST['device_ip'] ?? null;
    $file = $_FILES['device_image'];

    if (!$deviceIP || $file['error'] !== UPLOAD_ERR_OK) {
        die("Gagal mengunggah file.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Format file harus JPG, JPEG, PNG, atau WEBP.");
    }

    $newFileName = 'dev_' . md5($deviceIP . time()) . '.' . $ext;
    $targetPath = 'uploads/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Update kolom image di database berdasarkan IP Perangkat
        $stmt = $pdo->prepare("UPDATE monitoring_logs SET image = ? WHERE device_ip = ?");
        $stmt->execute([$newFileName, $deviceIP]);

        // Redirect kembali ke halaman utama
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        echo "Gagal menyimpan file ke folder uploads.";
    }
}
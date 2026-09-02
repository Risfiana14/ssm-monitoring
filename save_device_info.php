<?php
// save_device_info.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceIP = $_POST['device_ip'] ?? null;
    $location = $_POST['location'] ?? null; // Menangkap lokasi gerbong
    $notes    = trim($_POST['notes'] ?? '');

    if (!$deviceIP || !$location) {
        die("Device IP atau Lokasi Gerbong tidak ditemukan.");
    }

    try {
        // 1. Update catatan khusus untuk gerbong dan IP perangkat ini saja
        $stmtNotes = $pdo->prepare("UPDATE monitoring_logs SET notes = ? WHERE location = ? AND device_ip = ?");
        $stmtNotes->execute([$notes, $location, $deviceIP]);

        // 2. Jika ada unggahan gambar baru
        if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] === UPLOAD_ERR_OK) {
            
            // Cek jumlah upload spesifik per gerbong dan per IP
            $stmtCheck = $pdo->prepare("SELECT upload_count FROM monitoring_logs WHERE location = ? AND device_ip = ? LIMIT 1");
            $stmtCheck->execute([$location, $deviceIP]);
            $deviceData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $currentCount = (int)($deviceData['upload_count'] ?? 0);

            if ($currentCount >= 4) {
                echo "<script>alert('Batas maksimal upload foto (4 kali) untuk gerbong ini telah tercapai!'); window.history.back();</script>";
                exit;
            }

            $file = $_FILES['device_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Nama file dibuat unik menyertakan gerbong dan IP
                $newFileName = 'dev_' . md5($location . '_' . $deviceIP . '_' . time()) . '.' . $ext;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $newCount = $currentCount + 1;
                    
                    // Update gambar, hitungan upload, dan timestamp HANYA pada gerbong yang diklik
                    $stmtImg = $pdo->prepare("
                        UPDATE monitoring_logs 
                        SET image = ?, 
                            upload_count = ?, 
                            image_updated_at = NOW() 
                        WHERE location = ? AND device_ip = ?
                    ");
                    $stmtImg->execute([$newFileName, $newCount, $location, $deviceIP]);
                }
            }
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;

    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
}
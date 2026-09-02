<?php
// save_device_info.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceIP = $_POST['device_ip'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$deviceIP) {
        die("Device IP tidak ditemukan.");
    }

    try {
        // Update catatan pada semua log perangkat ini
        $stmtNotes = $pdo->prepare("UPDATE monitoring_logs SET notes = ? WHERE device_ip = ?");
        $stmtNotes->execute([$notes, $deviceIP]);

        // Jika ada unggahan gambar baru
        if (isset($_FILES['device_image']) && $_FILES['device_image']['error'] === UPLOAD_ERR_OK) {
            
            $stmtCheck = $pdo->prepare("SELECT MAX(upload_count) as total FROM monitoring_logs WHERE device_ip = ?");
            $stmtCheck->execute([$deviceIP]);
            $deviceData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $currentCount = (int)($deviceData['total'] ?? 0);

            if ($currentCount >= 4) {
                echo "<script>alert('Batas maksimal upload foto (4 kali) telah tercapai!'); window.history.back();</script>";
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

                $newFileName = 'dev_' . md5($deviceIP . time()) . '.' . $ext;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $newCount = $currentCount + 1;
                    $stmtImg = $pdo->prepare("
                        UPDATE monitoring_logs 
                        SET image = ?, 
                            upload_count = ?, 
                            image_updated_at = NOW() 
                        WHERE device_ip = ?
                    ");
                    $stmtImg->execute([$newFileName, $newCount, $deviceIP]);
                }
            }
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;

    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
}
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

        // =========================================================
        // AMBIL DATA DEVICE YANG SEDANG AKTIF
        // =========================================================
        $stmtDevice = $pdo->prepare("
            SELECT device_name, device_type, status
            FROM monitoring_logs
            WHERE location = ? AND device_ip = ?
            LIMIT 1
        ");

        $stmtDevice->execute([$location, $deviceIP]);

        $deviceData = $stmtDevice->fetch(PDO::FETCH_ASSOC);


        // Menyimpan nama file gambar baru untuk histori
        $newFileName = null;


        // =========================================================
        // 1. UPDATE CATATAN TERBARU
        // =========================================================
        if ($notes !== '') {

            $stmtNotes = $pdo->prepare("
                UPDATE monitoring_logs 
                SET notes = ?, 
                    image_updated_at = NOW() 
                WHERE location = ? AND device_ip = ?
            ");

            $stmtNotes->execute([$notes, $location, $deviceIP]);
        }


        // =========================================================
        // 2. JIKA ADA UNGGAHAN GAMBAR BARU
        // =========================================================
        if (
            isset($_FILES['device_image']) &&
            $_FILES['device_image']['error'] === UPLOAD_ERR_OK
        ) {

            $file = $_FILES['device_image'];

            $ext = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {

                $uploadDir = __DIR__ . '/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Nama file dibuat unik menyertakan nomor sarana dan IP
                $newFileName = 'dev_' .
                    md5(
                        $location . '_' .
                        $deviceIP . '_' .
                        time()
                    ) .
                    '.' . $ext;

                $targetPath = $uploadDir . $newFileName;


                if (move_uploaded_file($file['tmp_name'], $targetPath)) {

                    $stmtImg = $pdo->prepare("
                        UPDATE monitoring_logs 
                        SET image = ?, 
                            image_updated_at = NOW() 
                        WHERE location = ? AND device_ip = ?
                    ");

                    $stmtImg->execute([
                        $newFileName,
                        $location,
                        $deviceIP
                    ]);
                }
            }
        }


        // =========================================================
        // 3. SIMPAN HISTORI
        // =========================================================
        // Hanya dibuat jika user benar-benar mengisi catatan
        // atau mengunggah gambar baru.
        if ($notes !== '' || $newFileName !== null) {

            $historyStmt = $pdo->prepare("
                INSERT INTO device_history
                (
                    location,
                    device_name,
                    device_ip,
                    device_type,
                    status,
                    notes,
                    image,
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $historyStmt->execute([
                $location,
                $deviceData['device_name'] ?? null,
                $deviceIP,
                $deviceData['device_type'] ?? null,
                $deviceData['status'] ?? null,
                $notes !== '' ? $notes : null,
                $newFileName
            ]);
        }


        // =========================================================
        // KEMBALI KE HALAMAN SEBELUMNYA
        // =========================================================
        header(
            "Location: " .
            ($_SERVER['HTTP_REFERER'] ?? 'index.php')
        );

        exit;

    } catch (PDOException $e) {

        die("Error Database: " . $e->getMessage());

    }
}
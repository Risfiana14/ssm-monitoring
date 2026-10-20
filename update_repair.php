<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? null;
    $field = $_POST['field'] ?? null;
    $value = $_POST['value'] ?? '';

    // Izinkan kolom status dan repair_notes untuk diupdate
    $allowedFields = ['status', 'repair_notes'];

    if (!$id || !in_array($field, $allowedFields, true)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Parameter tidak valid.'
        ]);
        exit;
    }

    try {

        // =========================================================
        // 1. UPDATE DATA REPAIR HISTORY
        // =========================================================
        $stmt = $pdo->prepare("
            UPDATE repair_history
            SET {$field} = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$value, $id]);


        // =========================================================
        // 2. JIKA STATUS SUDAH DIPERBAIKI,
        //    SINKRONKAN KE MONITORING LOGS
        // =========================================================
        if ($field === 'status' && $value === 'Sudah diperbaiki') {

            // Ambil data laporan perbaikan
            $stmtRepair = $pdo->prepare("
                SELECT
                    location,
                    device_ip,
                    repair_notes,
                    image_after
                FROM repair_history
                WHERE id = ?
                LIMIT 1
            ");

            $stmtRepair->execute([$id]);

            $repair = $stmtRepair->fetch(PDO::FETCH_ASSOC);

            if ($repair) {

                // Catatan perbaikan yang akan ditampilkan
                // di Detail Device
                $repairNotes = trim($repair['repair_notes'] ?? '');

                // Jika repair_notes kosong, jangan menimpa
                // catatan lama di monitoring_logs
                if ($repairNotes !== '') {

                    $stmtMonitoring = $pdo->prepare("
                        UPDATE monitoring_logs
                        SET
                            notes = ?,
                            image_updated_at = NOW()
                        WHERE location = ?
                        AND device_ip = ?
                    ");

                    $stmtMonitoring->execute([
                        $repairNotes,
                        $repair['location'],
                        $repair['device_ip']
                    ]);

                } else {

                    // Jika tidak ada catatan perbaikan,
                    // tetap update waktu terakhir
                    $stmtMonitoring = $pdo->prepare("
                        UPDATE monitoring_logs
                        SET image_updated_at = NOW()
                        WHERE location = ?
                        AND device_ip = ?
                    ");

                    $stmtMonitoring->execute([
                        $repair['location'],
                        $repair['device_ip']
                    ]);
                }

                // =====================================================
                // Jika sudah ada gambar sesudah diperbaiki,
                // jadikan gambar tersebut sebagai foto terbaru device
                // =====================================================
                if (!empty($repair['image_after'])) {

                    $stmtImage = $pdo->prepare("
                        UPDATE monitoring_logs
                        SET
                            image = ?,
                            image_updated_at = NOW()
                        WHERE location = ?
                        AND device_ip = ?
                    ");

                    $stmtImage->execute([
                        $repair['image_after'],
                        $repair['location'],
                        $repair['device_ip']
                    ]);
                }
            }
        }


        echo json_encode([
            'status' => 'ok',
            'message' => 'Berhasil diperbarui.'
        ], JSON_UNESCAPED_UNICODE);


    } catch (PDOException $e) {

        http_response_code(500);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak diizinkan.'
    ]);
}
?>
<?php

// Inisialisasi API & Database
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta'); // Memastikan jam server cocok dengan WIB

require_once 'db.php';

$trainset = $_GET['trainset'] ?? 'DAOP_8';
try {
    if ($trainset === 'DAOP_8' || $trainset === 'ALL' || empty($trainset)) {
        $query = "
            SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image, upload_count, image_updated_at, notes, internet_status
            FROM monitoring_logs
            ORDER BY id ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    } else {
        $query = "
            SELECT id, device_name, device_ip, device_type, trainset, location, status, timestamp, image, upload_count, image_updated_at, notes, internet_status
            FROM monitoring_logs
            WHERE trainset = ?
            ORDER BY id ASC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$trainset]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapping Unik Perangkat per Gerbong (Location ID)
    $latestDevices = [];
    foreach ($rows as $row) {
        $key = $row['location'] . '_' . $row['device_name'];
        if (isset($latestDevices[$key])) {
            if (empty($row['notes']) || trim($row['notes']) === '') {
                $row['notes'] = $latestDevices[$key]['notes'] ?? null;
            }
            if (empty($row['image']) || trim($row['image']) === '') {
                $row['image'] = $latestDevices[$key]['image'] ?? null;
                $row['upload_count'] = $latestDevices[$key]['upload_count'] ?? 0;
                $row['image_updated_at'] = $latestDevices[$key]['image_updated_at'] ?? null;
            }
        }
        $latestDevices[$key] = $row;
    }
    $orderedNames = [
        'NVR', 'CCTV 1', 'CCTV 2', 'INDOOR 1', 'INDOOR 2', 'OUTDOOR 1', 'OUTDOOR 2', 'SOT TV 1', 
        'SOT TV 2', 'MINI PC', 'SWITCH', 'ROUTER', 'ROUTER MAKAN', 'MODEM', 'WIFI', 'PLCVCU', 'CCTV 3'
    ];

    $result = array_values($latestDevices);

    foreach ($result as $device) {
        $statusUpper = strtoupper(trim($device['status'] ?? ''));

        // Hanya proses status yang merupakan gangguan
        if (
            !empty($statusUpper)
            && $statusUpper !== 'ONLINE'
            && $statusUpper !== 'UP'
        ) {

            $loc = $device['location'] ?? '';
            $dIp = $device['device_ip'] ?? '';
            $dName = $device['device_name'] ?? '';
            $dType = $device['device_type'] ?? '';
            $deviceId = $device['id'] ?? 0;

            if (!empty($loc) && !empty($dIp)) {
                $stmtPrevious = $pdo->prepare("
                    SELECT status
                    FROM monitoring_logs
                    WHERE location = ?
                      AND device_ip = ?
                      AND id < ?
                    ORDER BY id DESC
                    LIMIT 1
                ");

                $stmtPrevious->execute([
                    $loc,
                    $dIp,
                    $deviceId
                ]);
                $previousStatus = $stmtPrevious->fetchColumn();
                $previousStatusUpper = strtoupper(
                    trim((string)$previousStatus)
                );

                $isNewTrouble = (
                    $previousStatus === false
                    || $previousStatusUpper === 'ONLINE'
                    || $previousStatusUpper === 'UP'
                );

                if ($isNewTrouble) {

                    $autoNotes =
                        "Terdeteksi otomatis oleh sistem (Status: "
                        . $statusUpper
                        . ")";

                    $insTrouble = $pdo->prepare("
                        INSERT INTO device_history (
                            location,
                            device_name,
                            device_ip,
                            device_type,
                            status,
                            notes,
                            created_at
                        )
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $insTrouble->execute([
                        $loc,
                        $dName,
                        $dIp,
                        $dType,
                        $statusUpper,
                        $autoNotes
                    ]);

                    $insRepair = $pdo->prepare("
                        INSERT INTO repair_history (
                            location,
                            device_name,
                            device_ip,
                            device_type,
                            status,
                            notes,
                            created_at
                        )
                        VALUES (
                            ?,
                            ?,
                            ?,
                            ?,
                            'Belum ditindaklanjuti',
                            ?,
                            NOW()
                        )
                    ");

                    $insRepair->execute([
                        $loc,
                        $dName,
                        $dIp,
                        $dType,
                        $autoNotes
                    ]);
                }
            }
        }
    }

    // logika khusus internet_status
    foreach ($result as &$device) {

        // Ambil langsung dari kolom internet_status database
        $dbNetStatus = strtoupper(
            trim($device['internet_status'] ?? '')
        );
        if ($dbNetStatus === 'INTERNET') {
            $device['internet_status'] = 'INTERNET';
        } else if (
            $dbNetStatus === 'NO_INTERNET'
            || $dbNetStatus === 'NO INTERNET'
        ) {
            $device['internet_status'] = 'NO INTERNET';
        } else {
            $device['internet_status'] = 'NO INTERNET';
        }
    }

    unset($device);
    usort($result, function ($a, $b) use ($orderedNames) {
        if ($a['location'] !== $b['location']) {
            return strcmp($a['location'], $b['location']);
        }
        $posA = array_search(
            $a['device_name'],
            $orderedNames
        );
        $posB = array_search(
            $b['device_name'],
            $orderedNames
        );
        $posA = ($posA === false) ? 99 : $posA;
        $posB = ($posB === false) ? 99 : $posB;
        return $posA - $posB;
    });
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

?>
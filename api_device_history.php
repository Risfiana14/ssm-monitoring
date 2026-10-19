<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    // Ambil parameter tanggal dari URL
    $start_date = isset($_GET['start_date'])
        ? trim($_GET['start_date'])
        : '';

    $end_date = isset($_GET['end_date'])
        ? trim($_GET['end_date'])
        : '';

    $sql = "
        SELECT
            dh.id,
            dh.location,
            dh.device_name,
            dh.device_ip,
            dh.device_type,
            dh.status,
            dh.notes,
            dh.image,
            dh.created_at
        FROM device_history AS dh
        WHERE (
            UPPER(TRIM(COALESCE(dh.status, ''))) NOT IN ('ONLINE', 'UP')
            OR TRIM(COALESCE(dh.notes, '')) <> ''
            OR TRIM(COALESCE(dh.image, '')) <> ''
        )
        ORDER BY
            dh.location ASC,
            dh.device_ip ASC,
            dh.created_at ASC,
            dh.id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $allData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // pisahkan history otomatis dan manual
    $automaticData = [];
    $manualData = [];

    foreach ($allData as $row) {
        $notes = trim((string)($row['notes'] ?? ''));
        $image = trim((string)($row['image'] ?? ''));

        $isAutomatic = (
            stripos($notes, 'Terdeteksi otomatis oleh sistem') === 0
        );

        if (!$isAutomatic && ($notes !== '' || $image !== '')) {
            $manualData[] = $row;
        } else {
            $automaticData[] = $row;
        }
    }

    $now = time();
    $latestAutomatic = [];

    foreach ($automaticData as $row) {

        $createdTime = strtotime($row['created_at']);

        if ($createdTime === false) {
            continue;
        }

        // history otomatis hanya ditampilkan dalam waktu 1x24 jam
        if (($now - $createdTime) > 86400) {
            continue;
        }

        $location = trim((string)($row['location'] ?? ''));
        $deviceIp = trim((string)($row['device_ip'] ?? ''));

        if ($location === '' || $deviceIp === '') {
            continue;
        }

        $deviceKey = $location . '|' . $deviceIp;

        if (
            !isset($latestAutomatic[$deviceKey])
            || strtotime($latestAutomatic[$deviceKey]['created_at']) < $createdTime
            || (
                strtotime($latestAutomatic[$deviceKey]['created_at']) === $createdTime
                && (int)$latestAutomatic[$deviceKey]['id'] < (int)$row['id']
            )
        ) {
            $latestAutomatic[$deviceKey] = $row;
        }
    }

    $data = array_merge(
        array_values($latestAutomatic),
        $manualData
    );

    // filter tanggal
    if (!empty($start_date) || !empty($end_date)) {

        $data = array_values(array_filter(
            $data,
            function ($row) use ($start_date, $end_date) {
                $createdDate = date(
                    'Y-m-d',
                    strtotime($row['created_at'])
                );
                if (
                    !empty($start_date)
                    && $createdDate < $start_date
                ) {
                    return false;
                }
                if (
                    !empty($end_date)
                    && $createdDate > $end_date
                ) {
                    return false;
                }
                return true;
            }
        ));
    }

    // urutkan data terbaru 
    usort(
        $data,
        function ($a, $b) {
            $timeA = strtotime($a['created_at']);
            $timeB = strtotime($b['created_at']);
            if ($timeA === $timeB) {
                return ((int)$b['id']) <=> ((int)$a['id']);
            }
            return $timeB <=> $timeA;
        }
    );

    echo json_encode([
        'status' => 'ok',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error.',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>
<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $period = $_GET['period'] ?? 'today';

    $startDate = null;
    $endDate = date('Y-m-d');

    switch ($period) {

        case '7days':
            $startDate = date('Y-m-d', strtotime('-6 days'));
            break;

        case '30days':
            $startDate = date('Y-m-d', strtotime('-29 days'));
            break;

        case 'custom':

            $startDate = trim($_GET['start_date'] ?? '');
            $endDate = trim($_GET['end_date'] ?? '');

            if ($startDate === '' || $endDate === '') {
                throw new Exception('Tanggal custom harus diisi.');
            }

            break;

        case 'today':
        default:
            $startDate = date('Y-m-d');
            break;
    }


    /*
     * =====================================================
     * TOTAL TROUBLE
     * =====================================================
     */

    $stmtTotal = $pdo->prepare("
        SELECT COUNT(*)
        FROM device_history
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");

    $stmtTotal->execute([
        $startDate,
        $endDate
    ]);

    $totalTrouble = (int)$stmtTotal->fetchColumn();


    /*
     * =====================================================
     * JUMLAH PERANGKAT YANG MENGALAMI TROUBLE
     * =====================================================
     */

    $stmtDevice = $pdo->prepare("
        SELECT COUNT(DISTINCT CONCAT(
            COALESCE(location, ''),
            '|',
            COALESCE(device_name, device_type, '')
        ))
        FROM device_history
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");

    $stmtDevice->execute([
        $startDate,
        $endDate
    ]);

    $deviceTrouble = (int)$stmtDevice->fetchColumn();


    /*
     * =====================================================
     * SUDAH DIPERBAIKI
     * =====================================================
     */

    $stmtRepaired = $pdo->prepare("
        SELECT COUNT(*)
        FROM repair_history
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND status = 'Sudah diperbaiki'
    ");

    $stmtRepaired->execute([
        $startDate,
        $endDate
    ]);

    $repaired = (int)$stmtRepaired->fetchColumn();


    /*
     * =====================================================
     * BELUM DIPERBAIKI
     * =====================================================
     */

    $stmtUnrepaired = $pdo->prepare("
        SELECT COUNT(*)
        FROM repair_history
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND status <> 'Sudah diperbaiki'
    ");

    $stmtUnrepaired->execute([
        $startDate,
        $endDate
    ]);

    $unrepaired = (int)$stmtUnrepaired->fetchColumn();


    /*
     * =====================================================
     * TREND TROUBLE PER HARI
     * =====================================================
     */

    $stmtTrend = $pdo->prepare("
        SELECT
            DATE(created_at) AS tanggal,
            COUNT(*) AS jumlah
        FROM device_history
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY tanggal ASC
    ");

    $stmtTrend->execute([
        $startDate,
        $endDate
    ]);

    $trend = $stmtTrend->fetchAll(PDO::FETCH_ASSOC);

    // Perangkat paling sering mengalami trouble
    $stmtDeviceRanking = $pdo->prepare("
        SELECT
            COALESCE(device_name, device_type, 'Tidak diketahui') AS device,
            COUNT(*) AS jumlah
        FROM device_history
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY COALESCE(device_name, device_type, 'Tidak diketahui')
        ORDER BY jumlah DESC
        LIMIT 10
    ");

    $stmtDeviceRanking->execute([
        $startDate,
        $endDate
    ]);

    $deviceRanking = $stmtDeviceRanking->fetchAll(PDO::FETCH_ASSOC);


    /*
     * =====================================================
     * KERETA PALING SERING TROUBLE
     * =====================================================
     */

    $stmtTrainRanking = $pdo->prepare("
        SELECT
            location,
            COUNT(*) AS jumlah
        FROM device_history
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND location IS NOT NULL
        AND TRIM(location) <> ''
        GROUP BY location
        ORDER BY jumlah DESC
        LIMIT 10
    ");

    $stmtTrainRanking->execute([
        $startDate,
        $endDate
    ]);

    $trainRanking = $stmtTrainRanking->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'status' => 'ok',

        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ],

        'summary' => [
            'total_trouble' => $totalTrouble,
            'device_trouble' => $deviceTrouble,
            'repaired' => $repaired,
            'unrepaired' => $unrepaired
        ],

        'trend' => $trend,

        'device_ranking' => $deviceRanking,

        'train_ranking' => $trainRanking

    ], JSON_UNESCAPED_UNICODE);


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
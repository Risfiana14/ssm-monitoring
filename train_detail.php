<?php
$tsCode = $_GET['id'] ?? 'TS-04';
$tsNumber = filter_var($tsCode, FILTER_SANITIZE_NUMBER_INT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM Dashboard - Trainset <?= sprintf("%02d", $tsNumber); ?></title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #163673;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .header-nav {
            padding: 15px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Card Gerbong Utama */
        .car-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            height: 380px; /* Tinggi seragam untuk semua gerbong */
            display: flex;
            flex-direction: column;
            overflow: hidden;
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .car-header {
            background: rgba(0, 0, 0, 0.2);
            padding: 12px 18px;
            font-weight: 700;
            font-size: 1.05rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .car-body {
            padding: 12px;
            flex-grow: 1;
            overflow-y: auto; /* Scroll rapi jika item sangat banyak */
        }

        /* Custom Scrollbar Tipis */
        .car-body::-webkit-scrollbar {
            width: 5px;
        }
        .car-body::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        .car-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* Container Item Perangkat (2 Kolom Grid) */
        .device-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Otomatis terbagi 2 kolom sejajar */
            gap: 8px;
        }

        /* Box Item Perangkat */
        .device-item {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 8px 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }

        .device-item:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .device-name {
            font-size: 0.75rem;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 110px;
        }

        .device-ip {
            font-size: 0.65rem;
            color: #a0aec0;
        }

        /* Indikator Status Dot */
        .dot-status {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
            background-color: #718096;
        }

        .dot-online {
            background-color: #28a745;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.8);
        }

        .dot-offline {
            background-color: #dc3545;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.8);
        }

        .dot-warning {
            background-color: #fd7e14;
            box-shadow: 0 0 8px rgba(253, 126, 20, 0.8);
        }

        .no-device-text {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #a0aec0;
            font-style: italic;
            font-size: 0.85rem;
        }

        .time-pill {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.8rem;
            color: #cbd5e0;
            display: inline-block;
        }
    </style>
</head>
<body class="p-3 p-md-4">

    <!-- Header Navigation -->
    <div class="header-nav mb-4">
        <a href="index.php" class="btn-back" title="Kembali ke Dashboard">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div class="text-center">
            <h3 class="fw-bold mb-0">🚆 Trainset <?= sprintf("%02d", $tsNumber); ?></h3>
            <small class="text-light opacity-75">Detail Monitoring Device - <?= htmlspecialchars($tsCode); ?></small>
        </div>
        <div style="width: 40px;"></div>
    </div>

    <!-- Grid 6 Gerbong (3 Kolom per baris di desktop) -->
    <div class="container-fluid">
        <div class="row g-3" id="car-grid">
            <?php 
            $cars = ['MC1', 'M1', 'T1', 'T2', 'M2', 'MC2'];
            foreach ($cars as $car): 
            ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="car-card">
                        <div class="car-header">
                            <span>Gerbong <?= $car; ?></span>
                            <span class="dot-status" id="dot-header-<?= $car; ?>"></span>
                        </div>
                        <div class="car-body" id="body-<?= $car; ?>">
                            <div class="no-device-text">Scanning devices for <?= $car; ?>...</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Last Update -->
        <div class="text-center mt-4">
            <div class="time-pill">
                <i class="bi bi-clock me-1"></i> Last update: <span id="last-update-time">No data</span>
            </div>
        </div>
    </div>

    <script>
        const trainsetCode = "<?= htmlspecialchars($tsCode); ?>";

        function scanDetail() {
            fetch(`api_detail_status.php?trainset=${trainsetCode}`)
                .then(res => res.json())
                .then(data => {
                    const cars = ['MC1', 'M1', 'T1', 'T2', 'M2', 'MC2'];
                    const carDataMap = {};
                    let latestTime = 'No data';

                    data.forEach(item => {
                        if (!carDataMap[item.location]) carDataMap[item.location] = [];
                        carDataMap[item.location].push(item);
                        latestTime = item.timestamp;
                    });

                    document.getElementById('last-update-time').innerText = latestTime;

                    cars.forEach(car => {
                        const bodyElem = document.getElementById(`body-${car}`);
                        const dotHeader = document.getElementById(`dot-header-${car}`);

                        if (carDataMap[car] && carDataMap[car].length > 0) {
                            let itemsHTML = '';
                            let hasOffline = false;
                            let hasWarning = false;

                            carDataMap[car].forEach(dev => {
                                const st = (dev.status || '').toUpperCase();
                                let dotClass = 'dot-offline';

                                if (st === 'ONLINE' || st === 'UP') {
                                    dotClass = 'dot-online';
                                } else if (st === 'WARNING') {
                                    dotClass = 'dot-warning';
                                    hasWarning = true;
                                } else {
                                    hasOffline = true;
                                }

                                itemsHTML += `
                                    <div class="device-item">
                                        <div class="me-2" style="overflow: hidden;">
                                            <div class="device-name" title="${dev.device_name || dev.device_type}">${dev.device_name || dev.device_type}</div>
                                            <div class="device-ip">${dev.device_ip}</div>
                                        </div>
                                        <span class="dot-status ${dotClass}"></span>
                                    </div>
                                `;
                            });

                            bodyElem.innerHTML = `<div class="device-grid">${itemsHTML}</div>`;

                            // Set warna dot header gerbong (Prioritas: Merah > Orange > Hijau)
                            if (hasOffline) {
                                dotHeader.className = 'dot-status dot-offline';
                            } else if (hasWarning) {
                                dotHeader.className = 'dot-status dot-warning';
                            } else {
                                dotHeader.className = 'dot-status dot-online';
                            }

                        } else {
                            bodyElem.innerHTML = `<div class="no-device-text">No devices found for ${car}</div>`;
                            dotHeader.className = 'dot-status';
                        }
                    });
                })
                .catch(err => console.error("Detail scan error:", err));
        }

        // Auto Scan Per Detik (1000 ms)
        setInterval(scanDetail, 1000);
        scanDetail();
    </script>
</body>
</html>
<?php
$tsCode = $_GET['id'] ?? 'TS-04';
$tsNumber = filter_var($tsCode, FILTER_SANITIZE_NUMBER_INT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM Dashboard - Trainset <?= htmlspecialchars($tsNumber); ?></title>
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
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-back {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background-color: rgba(255, 255, 255, 0.4);
            color: white;
        }

        .car-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            min-height: 240px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .car-header {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .car-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .no-device-text {
            color: #a0aec0;
            font-style: italic;
            font-size: 0.85rem;
            margin: auto;
        }

        .device-item {
            width: 100%;
            background: rgba(255, 255, 255, 0.07);
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        /* 3-Color Dot Status Indicators */
        .dot-status {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            background-color: #cbd5e0;
        }

        .dot-online  { background-color: #28a745; box-shadow: 0 0 6px #28a745; }
        .dot-warning { background-color: #fd7e14; box-shadow: 0 0 6px #fd7e14; }
        .dot-offline { background-color: #dc3545; box-shadow: 0 0 6px #dc3545; }

        .time-pill {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.75rem;
            color: #cbd5e0;
        }
    </style>
</head>
<body class="p-3">

    <!-- Header Navigation -->
    <div class="header-nav mb-3">
        <a href="index.php" class="btn-back"><i class="bi bi-arrow-left fs-5"></i></a>
        <div class="text-center">
            <h4 class="fw-bold mb-0">🚆 Trainset <?= sprintf("%02d", $tsNumber); ?></h4>
            <small class="text-light opacity-75">Detail Monitoring Device - <?= htmlspecialchars($tsCode); ?></small>
        </div>
        <div style="width: 38px;"></div>
    </div>

    <!-- Layout Grid 6 Gerbong -->
    <div class="container-fluid">
        <div class="row g-3" id="car-grid">
            <?php 
            $cars = ['MC1', 'M1', 'T1', 'T2', 'M2', 'MC2'];
            foreach ($cars as $car): 
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="car-card">
                        <div class="car-header">
                            <span>Gerbong <?= $car; ?></span>
                            <span class="dot-status" id="dot-header-<?= $car; ?>"></span>
                        </div>
                        <div class="car-body" id="body-<?= $car; ?>">
                            <span class="no-device-text">Scanning devices for <?= $car; ?>...</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Timestamp -->
        <div class="text-center mt-4">
            <span class="time-pill">
                <i class="bi bi-clock me-1"></i> Last update: <span id="last-update-time">No data</span>
            </span>
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

                    // Grouping data berdasarkan lokasi gerbong
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
                            bodyElem.innerHTML = '';
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

                                bodyElem.innerHTML += `
                                    <div class="device-item">
                                        <div>
                                            <strong>${dev.device_name || dev.device_type}</strong>
                                            <div style="font-size:0.7rem; color:#cbd5e0;">${dev.device_ip}</div>
                                        </div>
                                        <span class="dot-status ${dotClass}"></span>
                                    </div>
                                `;
                            });

                            // Set warna dot header gerbong (Prioritas: Merah > Orange > Hijau)
                            if (hasOffline) {
                                dotHeader.className = 'dot-status dot-offline';
                            } else if (hasWarning) {
                                dotHeader.className = 'dot-status dot-warning';
                            } else {
                                dotHeader.className = 'dot-status dot-online';
                            }

                        } else {
                            bodyElem.innerHTML = `<span class="no-device-text">No devices found for ${car}</span>`;
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
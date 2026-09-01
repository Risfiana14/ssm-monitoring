<?php
$tsCode = $_GET['id'] ?? 'TS-01';
$tsNumber = filter_var($tsCode, FILTER_SANITIZE_NUMBER_INT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM Dashboard - Trainset <?= htmlspecialchars($tsNumber); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #1a49a8;
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
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .car-card {
            background-color: #3b5998;
            border-radius: 12px;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .car-header {
            background-color: #556b94;
            padding: 10px 15px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .car-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .no-device-text {
            color: #a0aec0;
            font-style: italic;
            font-size: 0.9rem;
        }

        .device-item {
            width: 100%;
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .dot-status {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            background-color: #cbd5e0;
        }

        .dot-online { background-color: #28a745; box-shadow: 0 0 6px #28a745; }
        .dot-offline { background-color: #dc3545; box-shadow: 0 0 6px #dc3545; }

        .time-pill {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.75rem;
            color: #cbd5e0;
        }
    </style>
</head>
<body class="p-3">

    <!-- Header Navigation -->
    <div class="header-nav mb-3">
        <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <div class="text-center">
            <h4 class="fw-bold mb-0">🚆 Trainset <?= sprintf("%02d", $tsNumber); ?></h4>
            <small class="text-light opacity-75">Detail Monitoring Device - <?= htmlspecialchars($tsCode); ?></small>
        </div>
        <div style="width: 36px;"></div> <!-- Spacer -->
    </div>

    <!-- Layout Grid 6 Gerbong -->
    <div class="container-fluid">
        <div class="row g-3" id="car-grid">
            <?php 
            $cars = ['MC1', 'M1', 'T1', 'T2', 'M2', 'MC2'];
            foreach ($cars as $car): 
            ?>
                <div class="col-md-4">
                    <div class="car-card">
                        <div class="car-header">
                            <span><?= $car; ?></span>
                            <span class="dot-status" id="dot-header-<?= $car; ?>"></span>
                        </div>
                        <div class="car-body" id="body-<?= $car; ?>">
                            <span class="no-device-text">No devices found for <?= $car; ?></span>
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

                    // Kelompokkan data berdasarkan lokasi gerbong
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

                            carDataMap[car].forEach(dev => {
                                const isUp = dev.status === 'ONLINE' || dev.status === 'UP';
                                if (!isUp) hasOffline = true;

                                bodyElem.innerHTML += `
                                    <div class="device-item">
                                        <div>
                                            <strong>${dev.device_name || dev.device_type}</strong>
                                            <div style="font-size:0.7rem; color:#cbd5e0;">${dev.device_ip}</div>
                                        </div>
                                        <span class="dot-status ${isUp ? 'dot-online' : 'dot-offline'}"></span>
                                    </div>
                                `;
                            });

                            dotHeader.className = `dot-status ${hasOffline ? 'dot-offline' : 'dot-online'}`;
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
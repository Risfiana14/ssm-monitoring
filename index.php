<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM DASHBOARD - Train Monitoring System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #163673;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-header {
            padding: 20px 0;
            text-align: center;
        }

        .dashboard-title {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 2.2rem;
            margin-bottom: 2px;
        }

        .dashboard-subtitle {
            font-size: 0.95rem;
            color: #a0aec0;
            margin-bottom: 0;
        }

        .train-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 16px;
            backdrop-filter: blur(5px);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .train-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .ts-title {
            font-weight: 700;
            font-size: 1rem;
            color: #e2e8f0;
        }

        .badge-status {
            font-size: 0.65rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Pemetaan Warna Status Header Badge */
        .badge-nodata { background-color: #4a5568; color: #cbd5e0; }
        .badge-online { background-color: #28a745; color: #ffffff; }
        .badge-warning { background-color: #fd7e14; color: #ffffff; }
        .badge-offline { background-color: #dc3545; color: #ffffff; }

        .car-box {
            background-color: #4a5568;
            color: #a0aec0;
            border-radius: 6px;
            padding: 8px 0;
            text-align: center;
            font-size: 0.7rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        /* Pemetaan Warna Status Gerbong (3 Warna) */
        .car-box.up {
            background-color: #28a745 !important;
            color: #ffffff !important;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
        }

        .car-box.warning {
            background-color: #fd7e14 !important;
            color: #ffffff !important;
            box-shadow: 0 0 8px rgba(253, 126, 20, 0.6);
        }

        .car-box.down {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.6);
        }

        .last-update {
            font-size: 0.72rem;
            color: #94a3b8;
        }
    </style>
</head>
<body class="p-3 p-md-4">

    <!-- Header Dashboard -->
    <div class="dashboard-header mb-4">
        <h1 class="dashboard-title">SSM DASHBOARD</h1>
        <p class="dashboard-subtitle">Train Monitoring System</p>
    </div>

    <!-- Grid Container 16 Trainset -->
    <div class="container-fluid">
        <div class="row g-3" id="trainset-grid">
            <!-- 16 Trainset Cards di-generate oleh JavaScript -->
        </div>
    </div>

    <script>
        // 1. Generate Layout 16 Trainset (TS-01 s.d TS-16)
        const grid = document.getElementById('trainset-grid');
        const cars = ['MC1', 'M1', 'T1', 'T2', 'M2', 'MC2'];

        for (let i = 1; i <= 16; i++) {
            const tsCode = `TS-${String(i).padStart(2, '0')}`;
            
            let carBoxesHTML = '';
            cars.forEach(car => {
                carBoxesHTML += `
                    <div class="col">
                        <div class="car-box" id="car-${tsCode}-${car}">${car}</div>
                    </div>
                `;
            });

            grid.innerHTML += `
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="train-card" onclick="window.location.href='train_detail.php?id=${tsCode}'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="ts-title">${tsCode}</span>
                            <span class="badge-status badge-nodata" id="badge-${tsCode}">NO DATA</span>
                        </div>
            
                        <div class="row g-1 mb-3">
                            ${carBoxesHTML}
                        </div>

                        <div class="text-center last-update">
                            Last update: <span id="time-${tsCode}">No data</span>
                        </div>
                    </div>
                </div>
            `;
        }

        // 2. Fungsi Auto Scan Fetch Data Per Detik
        function scanData() {
            fetch('api_status.php')
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) return;

                    // Grouping status per Trainset dan per Gerbong
                    const trainsetMap = {};

                    data.forEach(item => {
                        const ts = item.trainset; // e.g. TS-04
                        const loc = item.location; // e.g. MC1
                        const status = item.status.toUpperCase(); // ONLINE / WARNING / OFFLINE

                        if (!trainsetMap[ts]) {
                            trainsetMap[ts] = {
                                lastTime: item.timestamp,
                                cars: {},
                                hasOffline: false,
                                hasWarning: false
                            };
                        }

                        trainsetMap[ts].cars[loc] = status;

                        if (status === 'OFFLINE' || status === 'DOWN') {
                            trainsetMap[ts].hasOffline = true;
                        } else if (status === 'WARNING') {
                            trainsetMap[ts].hasWarning = true;
                        }
                    });

                    // Render Pembaruan Visual di Dashboard
                    Object.keys(trainsetMap).forEach(ts => {
                        const tsData = trainsetMap[ts];

                        // Update Timestamp
                        const timeElem = document.getElementById(`time-${ts}`);
                        if (timeElem) timeElem.innerText = tsData.lastTime;

                        // Update Badge Status Trainset (Merah / Orange / Hijau)
                        const badgeElem = document.getElementById(`badge-${ts}`);
                        if (badgeElem) {
                            badgeElem.classList.remove('badge-nodata', 'badge-online', 'badge-offline', 'badge-warning');
                            if (tsData.hasOffline) {
                                badgeElem.classList.add('badge-offline');
                                badgeElem.innerText = 'OFFLINE';
                            } else if (tsData.hasWarning) {
                                badgeElem.classList.add('badge-warning');
                                badgeElem.innerText = 'WARNING';
                            } else {
                                badgeElem.classList.add('badge-online');
                                badgeElem.innerText = 'ONLINE';
                            }
                        }

                        // Update Warna Kotak Gerbong (Hijau / Orange / Merah)
                        Object.keys(tsData.cars).forEach(car => {
                            const carElem = document.getElementById(`car-${ts}-${car}`);
                            if (carElem) {
                                carElem.classList.remove('up', 'down', 'warning');
                                const st = tsData.cars[car];

                                if (st === 'ONLINE' || st === 'UP') {
                                    carElem.classList.add('up');       // Hijau
                                } else if (st === 'WARNING') {
                                    carElem.classList.add('warning');  // Orange
                                } else if (st === 'OFFLINE' || st === 'DOWN') {
                                    carElem.classList.add('down');     // Merah
                                }
                            }
                        });
                    });
                })
                .catch(err => console.error("Auto scan error:", err));
        }

        // Jalankan auto scan per 1 detik (1000 ms)
        setInterval(scanData, 1000);
        scanData(); // Initial execution
    </script>
</body>
</html>
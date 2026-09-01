<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM DASHBOARD - Train Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a365d; color: #ffffff; font-family: sans-serif; }
        .train-card { background-color: #2b4c7e; border-radius: 12px; padding: 15px; border: 1px solid #3b629b; }
        .badge-nodata { background-color: #4a5568; color: #cbd5e0; font-size: 10px; padding: 2px 6px; border-radius: 4px; }
        .car-box { 
            background-color: #4a5568; border-radius: 6px; padding: 6px 2px; text-align: center; 
            font-size: 10px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; 
        }
        .car-box.up { background-color: #28a745 !important; color: white; }
        .car-box.down { background-color: #dc3545 !important; color: white; }
        .last-update { font-size: 11px; color: #a0aec0; }
    </style>
</head>
<body class="p-4">

    <div class="text-center mb-4">
        <h1 class="fw-bold tracking-wide">SSM DASHBOARD</h1>
        <p class="text-light opacity-75">Train Monitoring System (Auto Scanning Per Detik)</p>
    </div>

    <div class="container-fluid">
        <div class="row g-3" id="trainset-container">
            <!-- Trainset TS-04 Card Contoh -->
            <div class="col-md-3">
                <div class="train-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold fs-5">TS-04</span>
                        <span class="badge-nodata" id="status-badge-TS-04">NO DATA</span>
                    </div>
                    
                    <!-- 6 Gerbong/Car: MC1, M1, T1, T2, M2, MC2 -->
                    <div class="row g-1 text-center mb-3">
                        <div class="col"><div class="car-box" id="car-TS-04-MC1">MC1</div></div>
                        <div class="col"><div class="car-box" id="car-TS-04-M1">M1</div></div>
                        <div class="col"><div class="car-box" id="car-TS-04-T1">T1</div></div>
                        <div class="col"><div class="car-box" id="car-TS-04-T2">T2</div></div>
                        <div class="col"><div class="car-box" id="car-TS-04-M2">M2</div></div>
                        <div class="col"><div class="car-box" id="car-TS-04-MC2">MC2</div></div>
                    </div>

                    <div class="text-center last-update">
                        Last update: <span id="time-TS-04">No data</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function autoScan() {
            fetch('api_status.php')
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) return;

                    // Mengelompokkan status gerbong
                    const carStatusMap = {};

                    data.forEach(item => {
                        const key = `${item.trainset}-${item.location}`; // e.g. TS-04-MC1
                        if (!carStatusMap[key] || item.status === 'OFFLINE') {
                            carStatusMap[key] = item.status;
                        }
                        
                        // Update status waktu terakhir
                        const timeElem = document.getElementById(`time-${item.trainset}`);
                        if (timeElem) timeElem.innerText = item.timestamp;
                        
                        const badgeElem = document.getElementById(`status-badge-${item.trainset}`);
                        if (badgeElem) {
                            badgeElem.innerText = "ONLINE";
                            badgeElem.style.backgroundColor = "#28a745";
                        }
                    });

                    // Render warna indikator pada tiap gerbong (MC1, M1, dll)
                    Object.keys(carStatusMap).forEach(key => {
                        const elem = document.getElementById(`car-${key}`);
                        if (elem) {
                            elem.classList.remove('up', 'down');
                            if (carStatusMap[key] === 'ONLINE' || carStatusMap[key] === 'UP') {
                                elem.classList.add('up');
                            } else {
                                elem.classList.add('down');
                            }
                        }
                    });
                })
                .catch(err => console.error("Scanning Error:", err));
        }

        // Jalankan pemindaian otomatis per 1 detik (1000 ms)
        setInterval(autoScan, 1000);
        autoScan();
    </script>
</body>
</html>
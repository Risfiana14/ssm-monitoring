<?php
$trainset = $_GET['id'] ?? 'Argo Wilis';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Perangkat - <?= htmlspecialchars($trainset); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #163673; color: #ffffff; font-family: 'Segoe UI', sans-serif; }
        .car-card {
            background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px; min-height: 250px; display: flex; flex-direction: column;
        }
        .car-header {
            background-color: rgba(255, 255, 255, 0.1); padding: 10px 15px;
            font-weight: 700; display: flex; justify-content: space-between; align-items: center;
        }
        .device-item {
            background: rgba(255, 255, 255, 0.08); padding: 6px 10px; border-radius: 6px;
            margin-bottom: 5px; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center;
        }
        .dot-status { height: 10px; width: 10px; border-radius: 50%; display: inline-block; }
        .dot-online { background-color: #28a745; box-shadow: 0 0 6px #28a745; }
        .dot-warning { background-color: #fd7e14; box-shadow: 0 0 6px #fd7e14; }
        .dot-offline { background-color: #dc3545; box-shadow: 0 0 6px #dc3545; }
    </style>
</head>
<body class="p-4">

    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-light me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h3 class="fw-bold mb-0">🚆 Detail Perangkat Kereta: <?= htmlspecialchars($trainset); ?></h3>
    </div>

    <div class="row g-3" id="car-grid">
        <!-- 6 Gerbong Unik -->
        <?php 
        $uniqueCars = ['K102436', 'K102438', 'K102437', 'K102439', 'M102411', 'K302452'];
        foreach ($uniqueCars as $car): 
        ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="car-card">
                    <div class="car-header">
                        <span>Gerbong <?= $car; ?></span>
                        <span class="dot-status" id="dot-header-<?= $car; ?>"></span>
                    </div>
                    <div class="p-3 flex-grow-1" id="body-<?= $car; ?>">
                        <span class="text-light opacity-50 fst-italic">Memuat perangkat...</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        const trainsetName = "<?= htmlspecialchars($trainset); ?>";

        function loadDetail() {
            fetch(`api_detail_status.php?trainset=${encodeURIComponent(trainsetName)}`)
                .then(res => res.json())
                .then(data => {
                    const uniqueCars = ['K102436', 'K102438', 'K102437', 'K102439', 'M102411', 'K302452'];
                    const carMap = {};

                    data.forEach(item => {
                        if (!carMap[item.location]) carMap[item.location] = [];
                        carMap[item.location].push(item);
                    });

                    uniqueCars.forEach(car => {
                        const bodyElem = document.getElementById(`body-${car}`);
                        const dotHeader = document.getElementById(`dot-header-${car}`);

                        if (carMap[car] && carMap[car].length > 0) {
                            bodyElem.innerHTML = '';
                            let hasOffline = false, hasWarning = false;

                            carMap[car].forEach(dev => {
                                const st = (dev.status || '').toUpperCase();
                                let dotClass = 'dot-offline';
                                if (st === 'ONLINE' || st === 'UP') dotClass = 'dot-online';
                                else if (st === 'WARNING') { dotClass = 'dot-warning'; hasWarning = true; }
                                else hasOffline = true;

                                bodyElem.innerHTML += `
                                    <div class="device-item">
                                        <div>
                                            <span class="fw-bold">${dev.device_name}</span>
                                            <span class="text-light opacity-75 ms-2">(${dev.device_ip})</span>
                                        </div>
                                        <span class="dot-status ${dotClass}"></span>
                                    </div>
                                `;
                            });

                            if (hasOffline) dotHeader.className = 'dot-status dot-offline';
                            else if (hasWarning) dotHeader.className = 'dot-status dot-warning';
                            else dotHeader.className = 'dot-status dot-online';

                        } else {
                            bodyElem.innerHTML = `<span class="text-light opacity-50 fst-italic">Tidak ada perangkat aktif di ${car}</span>`;
                            dotHeader.className = 'dot-status';
                        }
                    });
                });
        }

        setInterval(loadDetail, 1000);
        loadDetail();
    </script>
</body>
</html>
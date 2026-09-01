<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSM DASHBOARD - Kereta Argo Wilis</title>
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

        .dashboard-header {
            padding: 20px 0 10px 0;
            text-align: center;
        }

        .dashboard-title {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 2rem;
            margin-bottom: 2px;
        }

        .train-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 20px;
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        .car-box {
            background-color: #4a5568;
            color: #ffffff;
            border-radius: 8px;
            padding: 12px 4px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25 ease;
            user-select: none;
        }

        .car-box:hover {
            transform: translateY(-3px);
            filter: brightness(1.15);
        }

        .car-box.active-car {
            border: 2px solid #ffffff;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.8);
        }

        /* Status Colors Gerbong */
        .car-box.up { background-color: #28a745 !important; }
        .car-box.warning { background-color: #fd7e14 !important; }
        .car-box.down { background-color: #dc3545 !important; }

        /* Panel Expansion 15 Device */
        .device-panel {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .device-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            width: 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .device-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateX(3px);
        }

        .dot-status {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-online { background-color: #28a745; box-shadow: 0 0 6px #28a745; }
        .dot-warning { background-color: #fd7e14; box-shadow: 0 0 6px #fd7e14; }
        .dot-offline { background-color: #dc3545; box-shadow: 0 0 6px #dc3545; }

        /* Styling Modal Pop-Up */
        .modal-content {
            background-color: #1e293b;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
        }
        
        .modal-header { border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .modal-footer { border-top: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="p-3 p-md-4">

    <!-- Header Dashboard -->
    <div class="dashboard-header mb-3">
        <h1 class="dashboard-title">SSM DASHBOARD</h1>
        <p class="text-light opacity-75 small mb-0">Real-Time Train Monitoring System</p>
    </div>

    <!-- Main Container -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                
                <!-- Card Utama Kereta Argo Wilis -->
                <div class="train-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0"><i class="bi bi-train-front me-2"></i>KERETA ARGO WILIS</h4>
                        <span class="badge bg-success px-3 py-2 rounded-pill fw-bold" id="badge-train">ONLINE</span>
                    </div>

                    <!-- 6 Kotak Gerbong Unik Kereta SS NG -->
                    <div class="row g-2 mb-2" id="car-boxes-container">
                        <!-- Gerbong di-render via JavaScript -->
                    </div>

                    <div class="text-center text-light opacity-75 mt-3" style="font-size: 0.78rem;">
                        <i class="bi bi-clock me-1"></i> Last update: <span id="last-update">No data</span>
                    </div>
                </div>

                <!-- Panel Expansion: 15 Nama Device (3 Baris Kebawah / 3 Kolom Responsif) -->
                <div class="device-panel" id="device-panel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-info">
                            <i class="bi bi-cpu me-2"></i>Daftar Perangkat Gerbong: <span id="selected-car-title" class="text-white"></span>
                        </h6>
                        <span class="small text-light opacity-75">Klik nama perangkat untuk melihat detail status</span>
                    </div>

                    <!-- Layout Grid 3 Kolom (15 Device disusun 3 baris kebawah secara responsif) -->
                    <div class="row g-2" id="device-grid">
                        <!-- 15 Device akan di-render di sini -->
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Pop-Up Detail Device -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalDeviceName">Detail Device</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-dark table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-light opacity-75">IP Address</td>
                                <td class="fw-bold text-end" id="modalDeviceIP">-</td>
                            </tr>
                            <tr>
                                <td class="text-light opacity-75">Tipe Perangkat</td>
                                <td class="fw-bold text-end text-uppercase" id="modalDeviceType">-</td>
                            </tr>
                            <tr>
                                <td class="text-light opacity-75">Lokasi Gerbong</td>
                                <td class="fw-bold text-end" id="modalDeviceLocation">-</td>
                            </tr>
                            <tr>
                                <td class="text-light opacity-75">Status Koneksi</td>
                                <td class="text-end" id="modalDeviceStatus">-</td>
                            </tr>
                            <tr>
                                <td class="text-light opacity-75">Kondisi Operasional</td>
                                <td class="text-end" id="modalDeviceState">-</td>
                            </tr>
                            <tr>
                                <td class="text-light opacity-75">Waktu Update Terakhir</td>
                                <td class="text-end small" id="modalDeviceTime">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uniqueCars = ['K102436', 'K102438', 'K102437', 'K102439', 'M102411', 'K302452'];
        let selectedCar = null;
        let globalDeviceData = [];
        const deviceModal = new bootstrap.Modal(document.getElementById('deviceModal'));

        // 1. Render 6 Kotak Gerbong Beranda
        const carContainer = document.getElementById('car-boxes-container');
        uniqueCars.forEach(car => {
            carContainer.innerHTML += `
                <div class="col-4 col-md-2">
                    <div class="car-box" id="car-${car}" onclick="selectCar('${car}')">
                        <div>${car}</div>
                    </div>
                </div>
            `;
        });

        // 2. Fungsi Pilih Gerbong (Menampilkan 15 Device dalam 3 Kolom Responsif)
        function selectCar(carCode) {
            selectedCar = carCode;

            // Highlight Gerbong Aktif
            uniqueCars.forEach(c => {
                const elem = document.getElementById(`car-${c}`);
                if (c === carCode) elem.classList.add('active-car');
                else elem.classList.remove('active-car');
            });

            document.getElementById('selected-car-title').innerText = carCode;
            document.getElementById('device-panel').style.display = 'block';

            renderDevices();
        }

        // 3. Render 15 Perangkat Gerbong Terpilih
        function renderDevices() {
            if (!selectedCar) return;

            const grid = document.getElementById('device-grid');
            grid.innerHTML = '';

            // Filter data perangkat sesuai gerbong yang dipilih
            const devices = globalDeviceData.filter(d => d.location === selectedCar);

            if (devices.length === 0) {
                grid.innerHTML = `<div class="col-12 text-center text-light opacity-50 py-3">Tidak ada data perangkat aktif untuk gerbong ${selectedCar}</div>`;
                return;
            }

            // Membagi 15 perangkat ke dalam 3 kolom responsif (5 perangkat per kolom kebawah)
            const col1 = devices.slice(0, 5);
            const col2 = devices.slice(5, 10);
            const col3 = devices.slice(10, 15);

            const columns = [col1, col2, col3];

            columns.forEach(colDevices => {
                let colHTML = `<div class="col-12 col-md-4"><div class="d-flex flex-column gap-2">`;
                colDevices.forEach(dev => {
                    const st = (dev.status || '').toUpperCase();
                    let dotClass = 'dot-offline';
                    if (st === 'ONLINE' || st === 'UP') dotClass = 'dot-online';
                    else if (st === 'WARNING') dotClass = 'dot-warning';

                    // Simpan data perangkat di attribute HTML untuk modal
                    colHTML += `
                        <button class="device-btn" onclick='showDeviceDetail(${JSON.stringify(dev)})'>
                            <span>${dev.device_name}</span>
                            <span class="dot-status ${dotClass}"></span>
                        </button>
                    `;
                });
                colHTML += `</div></div>`;
                grid.innerHTML += colHTML;
            });
        }

        // 4. Pop-up Modal Detail Saat Nama Device Dipencet
        function showDeviceDetail(dev) {
            document.getElementById('modalDeviceName').innerText = dev.device_name;
            document.getElementById('modalDeviceIP').innerText = dev.device_ip;
            document.getElementById('modalDeviceType').innerText = dev.device_type;
            document.getElementById('modalDeviceLocation').innerText = dev.location;
            document.getElementById('modalDeviceTime').innerText = dev.timestamp;

            const st = (dev.status || '').toUpperCase();
            const statusElem = document.getElementById('modalDeviceStatus');
            const stateElem = document.getElementById('modalDeviceState');

            if (st === 'ONLINE' || st === 'UP') {
                statusElem.innerHTML = `<span class="badge bg-success">ONLINE</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-success"><i class="bi bi-arrow-up-circle-fill me-1"></i> UP (Normal)</span>`;
            } else if (st === 'WARNING') {
                statusElem.innerHTML = `<span class="badge bg-warning text-dark">WARNING</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> WARNING (Siaga)</span>`;
            } else {
                statusElem.innerHTML = `<span class="badge bg-danger">OFFLINE</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-danger"><i class="bi bi-arrow-down-circle-fill me-1"></i> DOWN (Rusak / Terputus)</span>`;
            }

            deviceModal.show();
        }

        // 5. Auto Scan Fetch Data Real-Time
        function scanData() {
            fetch('api_detail_status.php?trainset=Argo%20Wilis')
                .then(res => res.json())
                .then(data => {
                    globalDeviceData = data;

                    if (data && data.length > 0) {
                        document.getElementById('last-update').innerText = data[0].timestamp;
                    }

                    // Map status warna tiap gerbong
                    const carStatusMap = {};
                    uniqueCars.forEach(c => carStatusMap[c] = { hasOffline: false, hasWarning: false, hasData: false });

                    data.forEach(item => {
                        if (carStatusMap[item.location]) {
                            carStatusMap[item.location].hasData = true;
                            const st = (item.status || '').toUpperCase();
                            if (st === 'OFFLINE' || st === 'DOWN') carStatusMap[item.location].hasOffline = true;
                            else if (st === 'WARNING') carStatusMap[item.location].hasWarning = true;
                        }
                    });

                    // Update warna kotak gerbong di beranda
                    uniqueCars.forEach(car => {
                        const carElem = document.getElementById(`car-${car}`);
                        if (carElem) {
                            carElem.classList.remove('up', 'warning', 'down');
                            const info = carStatusMap[car];
                            if (info.hasData) {
                                if (info.hasOffline) carElem.classList.add('down');
                                else if (info.hasWarning) carElem.classList.add('warning');
                                else carElem.classList.add('up');
                            }
                        }
                    });

                    // Refresh panel jika gerbong sedang dipilih
                    if (selectedCar) renderDevices();
                })
                .catch(err => console.error("Error scan:", err));
        }

        setInterval(scanData, 1000);
        scanData();
    </script>
</body>
</html>
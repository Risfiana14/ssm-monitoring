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
            padding: 15px 0 10px 0;
            text-align: center;
        }

        .dashboard-title {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 1.6rem;
            margin-bottom: 2px;
        }

        .car-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 14px;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            display: inline-block;
            width: 100%;
        }

        .car-header {
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .device-grid-container {
            display: grid;
            grid-template-columns: repeat(5, 38px);
            grid-template-rows: repeat(3, 38px);
            gap: 8px;
            justify-content: center;
            align-items: center;
            padding: 4px 0;
        }

        .device-box {
            background-color: #4a5568;
            color: #ffffff;
            border-radius: 8px;
            padding: 0;
            text-align: center;
            font-size: 0.6rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            border: none;
            
            width: 38px !important;
            height: 38px !important;
            aspect-ratio: 1 / 1 !important;
            flex-shrink: 0;
            
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
        }

        .device-box:hover {
            transform: scale(1.12);
            filter: brightness(1.25);
        }

        .device-box.st-online {
            background-color: #28a745 !important;
            color: #ffffff !important;
            box-shadow: 0 0 6px rgba(40, 167, 69, 0.6);
        }

        .device-box.st-warning {
            background-color: #fd7e14 !important;
            color: #ffffff !important;
            box-shadow: 0 0 6px rgba(253, 126, 20, 0.6);
        }

        .device-box.st-offline {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 0 6px rgba(220, 53, 69, 0.6);
        }

        .badge-status {
            font-size: 0.6rem;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 700;
        }

        .modal-content {
            background-color: #1e293b;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
        }
        
        .modal-header { border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .modal-footer { border-top: 1px solid rgba(255, 255, 255, 0.1); }

        .device-img-preview {
            max-height: 180px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="p-2 p-md-3">

    <div class="dashboard-header mb-3">
        <h1 class="dashboard-title">SSM DASHBOARD</h1>
        <p class="text-light opacity-75 small mb-1">Real-Time Train Monitoring System</p>
        <div class="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-50 px-3 py-1 rounded-pill small" style="font-size: 0.75rem;">
            <i class="bi bi-train-front text-info"></i>
            <span class="fw-bold">KERETA ARGO WILIS</span>
            <span class="text-muted">|</span>
            <i class="bi bi-clock text-warning"></i>
            <span>Last update: <span id="last-update">No data</span></span>
        </div>
    </div>

    <div class="container" style="max-width: 600px;">
        <div class="row g-3 justify-content-center" id="cars-grid">
            <!-- 6 Kartu Gerbong -->
        </div>
    </div>

    <!-- Modal Pop-Up Detail -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="save_device_info.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="device_ip" id="uploadDeviceIP">

                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold" id="modalDeviceName">Detail Device</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body p-3">
                        <table class="table table-dark table-borderless table-sm mb-3">
                            <tbody style="font-size: 0.8rem;">
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
                                    <td class="text-light opacity-75">Status Connection</td>
                                    <td class="text-end" id="modalDeviceStatus">-</td>
                                </tr>
                                <tr>
                                    <td class="text-light opacity-75">Kondisi Sistem</td>
                                    <td class="text-end" id="modalDeviceState">-</td>
                                </tr>
                                <tr>
                                    <td class="text-light opacity-75">Waktu Log / Photo</td>
                                    <td class="text-end small" id="modalDeviceTime">-</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- INPUT CATATAN PERANGKAT -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-light opacity-75 mb-1">
                                <i class="bi bi-journal-text me-1 text-warning"></i>Catatan Perangkat
                            </label>
                            <textarea name="notes" id="modalDeviceNotes" class="form-control form-control-sm bg-dark text-light border-secondary" rows="2" placeholder="Masukkan catatan / penanganan..."></textarea>
                        </div>

                        <hr class="my-2 border-secondary">

                        <!-- INPUT FOTO PERANGKAT -->
                        <div class="mt-2">
                            <label class="form-label fw-bold small text-light opacity-75 mb-1">
                                <i class="bi bi-image me-1 text-info"></i>Foto Fisik Perangkat
                            </label>
                            <div class="text-center mb-3">
                                <img id="modalDeviceImage" src="https://via.placeholder.com/300x160?text=Belum+Ada+Foto" class="device-img-preview" alt="Foto Perangkat">
                            </div>

                            <input type="file" name="device_image" id="inputDeviceImage" class="form-control form-control-sm bg-dark text-light border-secondary" accept="image/*">
                        </div>
                    </div>

                    <!-- PISAHKAN TOMBOL KE FOOTER UNTUK MERAPATKAN FITUR SIMPAN -->
                    <div class="modal-footer py-2 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm py-1 px-3" style="font-size:0.75rem;" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary btn-sm py-1 px-3" id="btnSubmitForm" style="font-size:0.75rem;">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uniqueCars = ['K102436', 'K102438', 'K102437', 'K102439', 'M102411', 'K302452'];
        let globalDeviceData = [];
        let activeModalIP = null; // Menjaga isi modal saat terbuka
        
        const deviceModalElem = document.getElementById('deviceModal');
        const deviceModal = new bootstrap.Modal(deviceModalElem);

        deviceModalElem.addEventListener('hidden.bs.modal', function () {
            activeModalIP = null; // Reset saat modal ditutup
        });

        function getShortName(fullName) {
            const name = (fullName || '').trim().toUpperCase();

            if (name.includes('NVR')) return 'NVR';
            if (name.includes('CAM 3') || name.includes('CCTV 3')) return 'CAM3';
            if (name.includes('CAM 1') || name.includes('CCTV 1')) return 'CAM1';
            if (name.includes('CAM 2') || name.includes('CCTV 2')) return 'CAM2';
            if (name.includes('INDOOR 1') || name.includes('RTI 1')) return 'IND1';
            if (name.includes('INDOOR 2') || name.includes('RTI 2')) return 'IND2';
            if (name.includes('OUTDOOR 1') || name.includes('RTO R')) return 'OUT1';
            if (name.includes('OUTDOOR 2') || name.includes('RTO L')) return 'OUT2';
            if (name.includes('TV 1') || name.includes('CSOT U1')) return 'TV1';
            if (name.includes('TV 2') || name.includes('CSOT U2')) return 'TV2';
            if (name.includes('MINI PC') || name.includes('CPU')) return 'MPC';
            if (name.includes('SWITCH')) return 'SW';
            if (name.includes('ROUTER')) return 'RTR';
            if (name.includes('MODEM')) return 'MDM';
            if (name.includes('WIFI') || name.includes('ACCESS POINT')) return 'AP';
            if (name.includes('PLSVCU') || name.includes('VCU')) return 'VCU';

            return name.substring(0, 4);
        }

        const grid = document.getElementById('cars-grid');
        uniqueCars.forEach(car => {
            grid.innerHTML += `
                <div class="col-12 col-sm-6 d-flex justify-content-center">
                    <div class="car-card">
                        <div class="car-header">
                            <span><i class="bi bi-distribute-vertical me-1 text-info"></i>Gerbong ${car}</span>
                            <span class="badge-status bg-secondary" id="badge-${car}">NO DATA</span>
                        </div>
                        <div class="device-grid-container" id="body-${car}">
                            <div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Memuat...</div>
                        </div>
                    </div>
                </div>
            `;
        });

        function showDeviceDetailByIP(deviceIP) {
            const dev = globalDeviceData.find(d => d.device_ip === deviceIP);
            if (!dev) return;

            activeModalIP = deviceIP;

            document.getElementById('modalDeviceName').innerText = dev.device_name || dev.device_type;
            document.getElementById('modalDeviceIP').innerText = dev.device_ip;
            document.getElementById('modalDeviceType').innerText = dev.device_type;
            document.getElementById('modalDeviceLocation').innerText = dev.location;
            document.getElementById('uploadDeviceIP').value = dev.device_ip;

            // AMBIL NOTES DARI MEMORI/DATABASE DENGAN BENAR
            document.getElementById('modalDeviceNotes').value = dev.notes ? dev.notes : '';

            const lastPhotoTime = dev.image_updated_at ? dev.image_updated_at : dev.timestamp;
            document.getElementById('modalDeviceTime').innerText = lastPhotoTime;

            const imgElem = document.getElementById('modalDeviceImage');
            if (dev.image && dev.image.trim() !== '') {
                imgElem.src = `uploads/${dev.image}`;
            } else {
                imgElem.src = 'https://via.placeholder.com/300x160?text=Belum+Ada+Foto';
            }

            const uploadBtn = document.getElementById('btnSubmitForm');
            const uploadInput = document.getElementById('inputDeviceImage');
            const uploadCount = parseInt(dev.upload_count || 0);

            if (uploadCount >= 4) {
                if (uploadInput) uploadInput.disabled = true;
                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = `<i class="bi bi-save me-1"></i>Simpan Catatan (Upload 4/4 Habis)`;
                }
            } else {
                if (uploadInput) uploadInput.disabled = false;
                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = `<i class="bi bi-save me-1"></i>Simpan (${uploadCount}/4 Upload)`;
                }
            }

            const st = (dev.status || '').toUpperCase();
            const statusElem = document.getElementById('modalDeviceStatus');
            const stateElem = document.getElementById('modalDeviceState');

            if (st === 'ONLINE' || st === 'UP') {
                statusElem.innerHTML = `<span class="badge bg-success">ONLINE</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-success">UP (Normal)</span>`;
            } else if (st === 'WARNING') {
                statusElem.innerHTML = `<span class="badge bg-warning text-dark">WARNING</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-warning">WARNING (Siaga)</span>`;
            } else {
                statusElem.innerHTML = `<span class="badge bg-danger">OFFLINE</span>`;
                stateElem.innerHTML = `<span class="fw-bold text-danger">DOWN (Rusak)</span>`;
            }

            deviceModal.show();
        }

        function renderAllCars() {
            uniqueCars.forEach(car => {
                const bodyElem = document.getElementById(`body-${car}`);
                const badgeElem = document.getElementById(`badge-${car}`);
                const devices = globalDeviceData.filter(d => d.location === car);

                if (devices.length === 0) {
                    bodyElem.innerHTML = `<div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Tidak ada data</div>`;
                    if (badgeElem) {
                        badgeElem.className = 'badge-status bg-secondary';
                        badgeElem.innerText = 'NO DATA';
                    }
                    return;
                }

                let hasOffline = false, hasWarning = false;
                let carHTML = '';

                devices.forEach(dev => {
                    const st = (dev.status || '').toUpperCase();
                    let stClass = 'st-offline';

                    if (st === 'ONLINE' || st === 'UP') {
                        stClass = 'st-online';
                    } else if (st === 'WARNING') {
                        stClass = 'st-warning';
                        hasWarning = true;
                    } else {
                        hasOffline = true;
                    }

                    const shortLabel = getShortName(dev.device_name);

                    carHTML += `
                        <button class="device-box ${stClass}" onclick="showDeviceDetailByIP('${dev.device_ip}')" title="${dev.device_name} (${dev.device_ip})">
                            ${shortLabel}
                        </button>
                    `;
                });

                bodyElem.innerHTML = carHTML;

                if (badgeElem) {
                    badgeElem.classList.remove('bg-secondary', 'bg-success', 'bg-warning', 'bg-danger');
                    if (hasOffline) {
                        badgeElem.classList.add('bg-danger');
                        badgeElem.innerText = 'OFFLINE';
                    } else if (hasWarning) {
                        badgeElem.classList.add('bg-warning', 'text-dark');
                        badgeElem.innerText = 'WARNING';
                    } else {
                        badgeElem.classList.add('bg-success');
                        badgeElem.innerText = 'ONLINE';
                    }
                }
            });
        }

        function scanData() {
            fetch('api_detail_status.php?trainset=Argo%20Wilis')
                .then(res => res.json())
                .then(data => {
                    globalDeviceData = data;

                    if (data && data.length > 0) {
                        document.getElementById('last-update').innerText = data[0].timestamp;
                    }

                    renderAllCars();
                })
                .catch(err => console.error("Error scan:", err));
        }

        scanData();
        setInterval(scanData, 1000);
    </script>
</body>
</html>
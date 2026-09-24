<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAILMAP - DAOP 8</title>
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
            font-size: 2.1rem;
            margin-bottom: 2px;
        }

        .search-container {
            max-width: 400px;
            margin: 10px auto 0 auto;
        }

        .search-input {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 20px !important;
            font-size: 0.85rem;
            padding-left: 38px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        #searchCarInput::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
            opacity: 1 !important;
        }

        .search-input:focus {
            border-color: #0dcaf0 !important;
            box-shadow: 0 0 8px rgba(13, 202, 240, 0.4) !important;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .search-container:focus-within {
            border-color: #0dcaf0 !important;
            box-shadow: 0 0 8px rgba(13, 202, 240, 0.4) !important;
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

        /* HEADER KARTU Kereta (RAPID & FLEKSIBEL) */
        .car-header {
            font-weight: 700;
            font-size: 0.82rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            gap: 4px;
        }

        .car-title-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
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
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Styling Tombol Delete yang baru, jelas, dan kontras */
        .btn-delete-car {
            background-color: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.4);
            color: #ff6b6b;
            border-radius: 6px;
            padding: 2px 7px;
            font-size: 0.7rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-delete-car:hover {
            background-color: #dc3545;
            color: #ffffff;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.6);
            transform: scale(1.08);
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

        .saved-notes-display {
            background-color: #212529;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.8rem;
            color: #ffffff;
            max-height: 80px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .input-notes-area {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border: 1px solid #0dcaf0 !important;
        }

        /* ---------------------------------------------------- */
        /* CSS RESPONSIVE & PENYESUAIAN HEADER MOBIL (2 KOLOM) */
        /* ---------------------------------------------------- */
        @media (max-width: 575.98px) {
            .car-card {
                padding: 8px 6px !important;
            }

            .car-header {
                font-size: 0.68rem !important;
                margin-bottom: 6px !important;
                padding-bottom: 2px !important;
            }

            .car-title-long {
                display: none !important;
            }

            .car-title-short {
                display: inline !important;
            }

            .device-grid-container {
                grid-template-columns: repeat(5, 28px) !important;
                grid-template-rows: repeat(3, 28px) !important;
                gap: 4px !important;
            }

            .device-box {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.48rem !important;
                border-radius: 5px !important;
            }

            .badge-status {
                font-size: 0.5rem !important;
                padding: 1px 4px !important;
            }
        }

        @media (min-width: 576px) {
            .car-title-short {
                display: none !important;
            }
        }
    </style>
</head>
<body class="p-2 p-md-3">

   <div class="dashboard-header mb-3">
    <!-- Tombol Navigasi Notifikasi dan Dashboard -->
        <div class="d-flex justify-content-between align-items-center px-3 px-md-5 mb-2">
            <a href="dashboard_main.php" class="btn btn-sm btn-outline-info text-light d-flex align-items-center gap-1" style="border-radius: 20px; font-size: 0.75rem;">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
            
            <!-- Tombol Lonceng dengan ID dan Badge -->
            <a href="notifications.php" class="btn btn-sm btn-outline-warning text-light position-relative d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%;" id="notifIconWrapper">
                <i class="bi bi-bell-fill"></i>
                <!-- Badge Angka Notifikasi -->
                <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; display: none;">
                    0
                </span>
            </a>
        </div>

    <h1 class="dashboard-title">RAILMAP</h1>
    
    <div class="d-inline-flex align-items-center gap-2 mb-2 my-2" style="font-size: 1rem;">
        <i class="bi bi-train-front text-info fs-5"></i>
        <span class="fw-bold tracking-wide">Real-Time Train Monitoring System</span>
    </div>

    <!-- Wrapper Tengah -->
    <div style="width: 100%; text-align: center;">
        <div style="display: inline-block; width: 520px; max-width: 95%;">
            
            <!-- KAPSUL UTUH DENGAN GARIS BORDER DAN BACKGROUND JELAS -->
            <div class="search-container position-relative d-flex align-items-center mb-0 px-3" style="width: 100%; background-color: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 50px; padding: 6px 12px; backdrop-filter: blur(5px);">
                
                <!-- Sisi Kiri: Dropdown Filter (F) -->
                <select id="statusFilterDropdown" class="form-select form-select-sm bg-transparent text-light border-0 shadow-none" style="width: 140px; cursor: pointer; font-size: 0.85rem;" onchange="filterCars()">
                    <option value="all" style="background-color: #1a233a; color: #fff;">Semua Status</option>
                    <option value="online" style="background-color: #1a233a; color: #fff;">Online</option>
                    <option value="warning" style="background-color: #1a233a; color: #fff;">Warning</option>
                    <option value="offline" style="background-color: #1a233a; color: #fff;">Offline</option>
                    <option value="internet" style="background-color: #1a233a; color: #fff;">Internet</option>
                    <option value="no internet" style="background-color: #1a233a; color: #fff;">No Internet</option>
                    <option value="no data" style="background-color: #1a233a; color: #fff;">No Data</option>
                </select>

                <!-- Garis Pemisah Tipis di Tengah -->
                <div style="width: 1px; height: 20px; background-color: rgba(255, 255, 255, 0.2); margin: 0 8px; flex-shrink: 0;"></div>

                <!-- Sisi Kanan: Kolom Input Search (S) -->
                <input type="text" id="searchCarInput" class="form-control form-control-sm border-0 bg-transparent text-light shadow-none ps-2" placeholder="Cari nomor kereta..." oninput="filterCars()" style="font-size: 0.85rem; box-shadow: none !important;">
                <!-- Ikon Search di Ujung Kanan -->
                <i class="bi bi-search text-light opacity-75 ps-2 pe-1" style="font-size: 0.85rem;"></i>

            </div>

        </div>
    </div>
</div>

    <!-- Container Utama Dashboard -->
    <div class="container-fluid px-2 px-md-4" style="max-width: 1400px;">
        <div class="row g-2 g-md-3 justify-content-center" id="cars-grid">
            <!-- Grid Kartu Kereta -->
        </div>
    </div>

    <!-- Modal Pop-Up Detail Status -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="save_device_info.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="device_ip" id="uploadDeviceIP">
                    <input type="hidden" name="location" id="uploadDeviceLocation">

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
                                    <td class="text-light opacity-75">Lokasi Kereta</td>
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

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-light opacity-75 mb-1">
                                <i class="bi bi-journal-text me-1 text-warning"></i>Catatan Perangkat
                            </label>
                            
                            <div class="mb-2">
                                <div class="saved-notes-display" id="modalDisplayNotes">
                                    <em class="opacity-50">Belum ada catatan tersimpan.</em>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-light opacity-75" style="font-size: 0.72rem;">
                                    <i class="bi bi-pencil-square me-1 text-info"></i>Isi Catatan Baru:
                                </span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.68rem;" onclick="clearNotesInput()" title="Hapus teks di kolom input">
                                    <i class="bi bi-trash me-1"></i>Clear Input
                                </button>
                            </div>

                            <textarea name="notes" id="modalDeviceNotes" class="form-control form-control-sm input-notes-area" rows="2" placeholder="Masukkan catatan penanganan baru..."></textarea>
                        </div>

                        <hr class="my-2 border-secondary">

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

    <!-- Modal Konfirmasi Delete yang Modern & Jelas -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-danger shadow-lg">
                <div class="modal-header border-bottom border-secondary bg-danger bg-opacity-25 py-2">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Konfirmasi Hapus
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <p class="mb-1 text-light small">Yakin ingin menghapus kereta:</p>
                    <h5 id="deleteCarTarget" class="fw-bold text-info mb-2">-</h5>
                    <p class="text-muted mb-0" style="font-size: 0.72rem;">Kereta akan disembunyikan dari dashboard, namun data log historis tetap aman.</p>
                </div>
                <div class="modal-footer border-top border-secondary py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm px-3" style="font-size:0.75rem;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm px-3" id="btnConfirmDelete" style="font-size:0.75rem;">
                        <i class="bi bi-trash-fill me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let uniqueCars = [];
        let globalDeviceData = [];
        
        function sortCarsByStatus() {
            uniqueCars.sort((a, b) => {
                let devA = globalDeviceData.filter(d => d.location === a);
                let devB = globalDeviceData.filter(d => d.location === b);

                let priorityA = 3; 
                let priorityB = 3;

                if (devA.length > 0) {
                    let hasOffA = devA.some(d => {
                        let st = (d.status || '').toUpperCase();
                        return st !== 'ONLINE' && st !== 'UP' && st !== 'WARNING';
                    });
                    let hasWarnA = devA.some(d => (d.status || '').toUpperCase() === 'WARNING');
                    if (hasOffA) priorityA = 1;       
                    else if (hasWarnA) priorityA = 2;  
                    else priorityA = 3;                
                } else {
                    priorityA = 4; 
                }

                if (devB.length > 0) {
                    let hasOffB = devB.some(d => {
                        let st = (d.status || '').toUpperCase();
                        return st !== 'ONLINE' && st !== 'UP' && st !== 'WARNING';
                    });
                    let hasWarnB = devB.some(d => (d.status || '').toUpperCase() === 'WARNING');
                    if (hasOffB) priorityB = 1;
                    else if (hasWarnB) priorityB = 2;
                    else priorityB = 3;
                } else {
                    priorityB = 4; 
                }

                return priorityA - priorityB;
            });
        }

        const deviceModalElem = document.getElementById('deviceModal');
        const deviceModal = new bootstrap.Modal(deviceModalElem);

        function getShortName(fullName) {
            const name = (fullName || '').trim().toUpperCase().replace(/_/g, ' ');

            if (name.includes('NVR')) return 'NVR';
            if (name.includes('CAM 3') || name.includes('CCTV 3')) return 'CAM3';
            if (name.includes('CAM 1') || name.includes('CCTV 1')) return 'CAM1';
            if (name.includes('CAM 2') || name.includes('CCTV 2')) return 'CAM2';
            if (name.includes('INDOOR 1') || name.includes('RTI 1')) return 'IND1';
            if (name.includes('INDOOR 2') || name.includes('RTI 2')) return 'IND2';
            if (name.includes('OUTDOOR 1') || name.includes('RTO R')) return 'OUT1';
            if (name.includes('OUTDOOR 2') || name.includes('RTO L')) return 'OUT2';
            if (name.includes('SOT TV 1') || name.includes('CSOT U1')) return 'TV1';
            if (name.includes('SOT TV 2') || name.includes('CSOT U2')) return 'TV2';
            if (name.includes('MINI PC') || name.includes('CPU')) return 'MPC';
            if (name.includes('SWITCH')) return 'SW';
            if (name.includes('ROUTER')) return 'RTR';
            if (name.includes('MODEM')) return 'MDM';
            if (name.includes('WIFI') || name.includes('ACCESS POINT')) return 'AP';
            if (name.includes('PLCVCU') || name.includes('VCU')) return 'VCU';

            return name.substring(0, 4);
        }

        let currentStatusFilter = 'all';

        function setStatusFilterDropdown(status) {
            currentStatusFilter = status;
            filterCars(); 
        }

        function filterCars() {
            const inputVal = document.getElementById('searchCarInput').value.trim().toLowerCase();
            const selectedStatus = document.getElementById('statusFilterDropdown').value.toLowerCase();
            const carElements = document.querySelectorAll('.car-wrapper');

            carElements.forEach(el => {
                const carID = el.getAttribute('data-car-id').toLowerCase();
                const numericOnly = carID.replace(/^[a-z]+/, '');
                
                const cardText = el.textContent.toLowerCase();

                const matchesSearch = carID.includes(inputVal) || numericOnly.includes(inputVal);

                let matchesStatus = true;
                if (selectedStatus === 'online') {
                    matchesStatus = cardText.includes('online');
                } else if (selectedStatus === 'warning') {
                    matchesStatus = cardText.includes('warning');
                } else if (selectedStatus === 'offline') {
                    matchesStatus = cardText.includes('offline');
                } else if (selectedStatus === 'internet') {
                    matchesStatus = cardText.includes('internet') && !cardText.includes('no internet');
                } else if (selectedStatus === 'no internet') {
                    matchesStatus = cardText.includes('no internet');
                } else if (selectedStatus === 'no data') {
                    matchesStatus = cardText.includes('no data');
                }

                if (matchesSearch && matchesStatus) {
                    el.style.setProperty('display', 'flex', 'important');
                } else {
                    el.style.setProperty('display', 'none', 'important');
                }
            });
        }

        function clearNotesInput() {
            document.getElementById('modalDeviceNotes').value = '';
            document.getElementById('modalDeviceNotes').focus();
        }

        function showDeviceDetailByLocationAndIP(location, deviceIP) {
            const dev = globalDeviceData.find(d => d.location === location && d.device_ip === deviceIP);
            if (!dev) return;

            document.getElementById('modalDeviceName').innerText = dev.device_name || dev.device_type;
            document.getElementById('modalDeviceIP').innerText = dev.device_ip;
            document.getElementById('modalDeviceType').innerText = dev.device_type;
            document.getElementById('modalDeviceLocation').innerText = dev.location;
            
            document.getElementById('uploadDeviceIP').value = dev.device_ip;
            document.getElementById('uploadDeviceLocation').value = dev.location;

            const displayNotesElem = document.getElementById('modalDisplayNotes');
            const notesInputElem = document.getElementById('modalDeviceNotes');

            if (dev.notes && dev.notes.trim() !== '') {
                displayNotesElem.innerText = dev.notes;
            } else {
                displayNotesElem.innerHTML = '<em class="opacity-50">Belum ada catatan tersimpan.</em>';
            }
            
            notesInputElem.value = '';

            const lastPhotoTime = dev.image_updated_at ? dev.image_updated_at : dev.timestamp;
            document.getElementById('modalDeviceTime').innerText = lastPhotoTime;

            const imgElem = document.getElementById('modalDeviceImage');
            if (dev.image && dev.image.trim() !== '') {
                imgElem.src = `uploads/${dev.image}`;
            } else {
                imgElem.src = 'https://via.placeholder.com/300x160?text=Belum+Ada+Foto';
            }

            // UPLOAD FOTO TANPA BATAS 
            const uploadBtn = document.getElementById('btnSubmitForm');
            const uploadInput = document.getElementById('inputDeviceImage');

            if (uploadInput) {
                uploadInput.disabled = false;
            }

            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = `<i class="bi bi-save me-1"></i>Simpan Perubahan`;
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

        function ipToInt(ip) {
            if (!ip) return 0;
            return ip.split('.').reduce((acc, octet) => ((acc << 8) + parseInt(octet, 10)), 0) >>> 0;
        }

        function renderAllCars() {
            const gridContainer = document.getElementById('cars-grid');
            gridContainer.innerHTML = '';
            
            uniqueCars.forEach(car => {
                gridContainer.innerHTML += `
                    <div class="col-6 col-md-4 col-xl-3 d-flex justify-content-center car-wrapper" data-car-id="${car}">
                        <div class="car-card">
                            <div class="car-header">
                                <span class="car-title-text">
                                    <i class="bi bi-distribute-vertical me-1 text-info"></i>
                                    <span class="car-title-long">${car}</span>
                                    <span class="car-title-short">${car}</span>
                                </span>
                                
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge-status bg-secondary" id="internet-badge-${car}">-</span>
                                    <span class="badge-status bg-secondary" id="badge-${car}">NO DATA</span>
                                    <button type="button" class="btn-delete-car ms-1"
                                            onclick="confirmDeleteCar('${car}')" title="Hapus kereta ini dari dashboard">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="device-grid-container" id="body-${car}">
                                <div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Memuat...</div>
                            </div>
                            <div class="text-center text-light opacity-75 mt-2 pt-1 border-top border-secondary border-opacity-25" style="font-size: 0.65rem;">
                                <i class="bi bi-clock me-1 text-warning"></i>Last update: <span id="time-${car}">-</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            uniqueCars.forEach(car => {
                const bodyElem = document.getElementById(`body-${car}`);
                const badgeElem = document.getElementById(`badge-${car}`);
                const netBadgeElem = document.getElementById(`internet-badge-${car}`);
                const timeElem = document.getElementById(`time-${car}`);
                
                let devices = globalDeviceData.filter(d => d.location === car);

                if (devices.length === 0) {
                    bodyElem.innerHTML = `<div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Tidak ada data</div>`;
                    if (badgeElem) {
                        badgeElem.className = 'badge-status bg-secondary';
                        badgeElem.innerText = 'NO DATA';
                    }
                    if (netBadgeElem) {
                        netBadgeElem.className = 'badge-status bg-secondary';
                        netBadgeElem.innerText = 'NO INTERNET';
                    }
                    if (timeElem) timeElem.innerText = '-';
                    return;
                }

                devices.sort((a, b) => ipToInt(a.device_ip) - ipToInt(b.device_ip));

                let hasOffline = false, hasWarning = false;
                let carHTML = '';
                let latestTimestamp = '';

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

                    const devTime = dev.image_updated_at || dev.timestamp;
                    if (devTime) {
                        if (!latestTimestamp || new Date(devTime) > new Date(latestTimestamp)) {
                            latestTimestamp = devTime;
                        }
                    }

                    const shortLabel = getShortName(dev.device_name);

                    carHTML += `
                        <button class="device-box ${stClass}" onclick="showDeviceDetailByLocationAndIP('${dev.location}', '${dev.device_ip}')" title="${dev.device_name} (${dev.device_ip})">
                            ${shortLabel}
                        </button>
                    `;
                });

                bodyElem.innerHTML = carHTML;

                if (timeElem) {
                    timeElem.innerText = latestTimestamp ? latestTimestamp : '-';
                }

                let carData = globalCarriagesData.find(c => c.location === car) || {};
                const carriageInternet = (carData.internet_status || 'NO_INTERNET').toUpperCase();
                const isInternetConnected = (carriageInternet === 'INTERNET');

                if (netBadgeElem) {
                    netBadgeElem.classList.remove('bg-secondary', 'bg-info', 'bg-dark', 'bg-danger');
                    if (isInternetConnected) {
                        netBadgeElem.classList.add('bg-info', 'text-dark');
                        netBadgeElem.innerText = 'INTERNET';
                    } else {
                        netBadgeElem.classList.add('bg-danger');
                        netBadgeElem.innerText = 'NO INTERNET';
                    }
                }

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

            filterCars();
        }
        
        function scanData() {
            fetch('api_detail_status.php?trainset=DAOP_8')
                .then(res => res.json())
                .then(data => {
                    globalDeviceData = data;
                    sortCarsByStatus(); 
                    renderAllCars();
                    checkAndTriggerNotifications(); // <-- Tambahkan pemanggil ini di sini
                })
                .catch(err => console.error("Error scan:", err));
        }

        let globalCarriagesData = [];

        function loadCarList() {
            return fetch('get_cars.php')
                .then(res => res.json())
                .then(cars => {
                    globalCarriagesData = cars; 
                    uniqueCars = cars.map(item => item.location);
                });
        }

        // Variabel dan fungsi interaktif untuk Modal Delete Modern
        let carToDelete = null;
        const deleteModalElem = document.getElementById('deleteConfirmModal');
        const deleteModal = new bootstrap.Modal(deleteModalElem);

        function confirmDeleteCar(car) {
            carToDelete = car;
            document.getElementById('deleteCarTarget').innerText = car;
            deleteModal.show();
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function () {
            if (!carToDelete) return;

            const car = carToDelete;
            deleteModal.hide();

            fetch('delete_car.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `location=${encodeURIComponent(car)}`
            })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'ok') {
                    uniqueCars = uniqueCars.filter(c => c !== car);
                    globalDeviceData = globalDeviceData.filter(d => d.location !== car);
                    renderAllCars();
                } else {
                    alert('Gagal menghapus: ' + (result.message || 'unknown error'));
                }
            })
            .catch(err => {
                console.error('Error delete car:', err);
                alert('Terjadi kesalahan saat menghapus kereta.');
            });
        });

        loadCarList().then(() => {
            scanData();
            setInterval(scanData, 1000);
        });

        setInterval(() => {
            loadCarList();
        }, 5000);

        // Fungsi untuk mencatat dan memicu notifikasi otomatis saat ada perangkat gangguan
        let previousAlertState = {};

        function checkAndTriggerNotifications() {
            // Ambil jumlah notifikasi saat ini di localStorage
            let currentNotifications = JSON.parse(localStorage.getItem('railmap_notifications') || '[]');

            uniqueCars.forEach(car => {
                let carData = globalCarriagesData.find(c => c.location === car) || {};
                let devices = globalDeviceData.filter(d => d.location === car);
                
                let hasOffline = devices.some(d => {
                    let st = (d.status || '').toUpperCase();
                    return st !== 'ONLINE' && st !== 'UP' && st !== 'WARNING';
                });
                
                let carriageInternet = (carData.internet_status || 'INTERNET').toUpperCase();
                let isNoInternet = (carriageInternet === 'NO_INTERNET');

                let isCurrentlyTroubled = hasOffline || isNoInternet;
                let lastState = previousAlertState[car];

                // Catat notifikasi jika:
                // 1. Perangkat sedang bermasalah, DAN
                // 2. Sebelumnya statusnya masih aman (normal) ATAU log di storage benar-benar kosong bersih
                if (isCurrentlyTroubled && (lastState === 'NORMAL' || lastState === undefined)) {
                    saveNotificationToStorage(car, hasOffline, isNoInternet);
                }

                // Perbarui status memori lokal per kereta
                previousAlertState[car] = isCurrentlyTroubled ? 'TROUBLE' : 'NORMAL';
            });
        }

        function saveNotificationToStorage(car, isOffline, isNoInternet) {
            let notifications = JSON.parse(localStorage.getItem('railmap_notifications') || '[]');
            
            let now = new Date();
            // Format jam dan tanggal secara eksplisit agar pasti berupa teks string
            let timeString = now.toLocaleTimeString('id-ID');
            let dateString = now.toLocaleDateString('id-ID');

            let msg = `Kereta ${car} mengalami gangguan: `;
            if (isNoInternet) msg += "Koneksi Internet Terputus (NO INTERNET). ";
            if (isOffline) msg += "Terdapat perangkat status OFFLINE.";

            // Simpan dengan properti 'time' dan 'date' yang jelas
            notifications.unshift({ 
                time: timeString, 
                date: dateString, 
                message: msg 
            });

            if (notifications.length > 50) notifications.pop();
            localStorage.setItem('railmap_notifications', JSON.stringify(notifications));
        }

        </script>
        <!-- Wadah Toast Pop-up di Pojok Kanan Atas -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;" id="toastContainer"></div>

        <script>
        // Fungsi untuk memperbarui jumlah badge pada ikon lonceng
        function updateNotificationBadge() {
            let notifications = JSON.parse(localStorage.getItem('railmap_notifications') || '[]');
            let badge = document.getElementById('notifBadge');
            
            if (notifications.length > 0) {
                badge.style.display = 'inline-block';
                badge.innerText = notifications.length;
            } else {
                badge.style.display = 'none';
            }
        }

        // Fungsi untuk menampilkan pop-up Toast di pojok kanan atas
        function showToastNotification(message) {
            const toastContainer = document.getElementById('toastContainer');
            
            // Buat elemen unik untuk toast
            const toastId = 'toast-' + Date.now();
            const toastHTML = `
                <div id="${toastId}" class="toast align-items-center text-white bg-dark border-danger border shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body py-2 px-3">
                            <div class="fw-bold text-danger mb-1" style="font-size: 0.75rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> PERINGATAN GANGGUAN
                            </div>
                            <div style="font-size: 0.8rem;">${message}</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const bsToast = new bootstrap.Toast(toastElement, { delay: 4000 }); // Muncul selama 4 detik
            bsToast.show();
            
            // Hapus elemen dari DOM setelah toast tertutup agar tidak menumpuk
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // Simulasi pengecekan data lokal secara berkala (atau panggil fungsi ini saat sistem Anda mendeteksi error)
        let lastNotificationCount = JSON.parse(localStorage.getItem('railmap_notifications') || '[]').length;

        function checkNewNotifications() {
            let notifications = JSON.parse(localStorage.getItem('railmap_notifications') || '[]');
            
            if (notifications.length > lastNotificationCount) {
                // Ambil notifikasi paling baru yang masuk
                let latestNotif = notifications[0]; 
                showToastNotification(latestNotif.message || "Terjadi gangguan pada sistem kereta.");
            }
            
            lastNotificationCount = notifications.length;
            updateNotificationBadge();
        }

        // Jalankan saat halaman pertama kali dimuat
        updateNotificationBadge();

        // Cek perubahan localStorage secara berkala (misal tiap 2 detik jika ada tab/proses lain yang memperbarui data)
        setInterval(checkNewNotifications, 2000);
        </script>
</body>
</html>
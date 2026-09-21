<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAILMAP - Kereta Argo Wilis</title>
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
            font-size: 2.1rem; /* Diperbesar */
            margin-bottom: 2px;
        }

        /* Search Bar Styling */
        .search-container {
            max-width: 320px;
            margin: 12px auto 0 auto;
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

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
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

        /* Penyesuaian Warna Read-Only (Samakan dengan Tabel Detail) */
        .saved-notes-display {
            background-color: #212529; /* Warna gelap sama seperti tabel detail */
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

        /* Penyesuaian Textarea Input Catatan Baru */
        .input-notes-area {
            background-color: #0f172a !important; /* Latar agak kontras untuk menandakan input aktif */
            color: #ffffff !important;
            border: 1px solid #0dcaf0 !important; /* Border terang highlight cyan */
        }

        /* ===== RAILMAP SIDEBAR - TAMBAHAN SAJA ===== */
        .railmap-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 245px;
            height: 100vh;
            background: #102b5c;
            border-right: 1px solid rgba(255,255,255,0.12);
            padding: 22px 14px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.12);
        }

        .sidebar-brand {
            text-align: center;
            padding: 2px 5px 22px;
            border-bottom: 1px solid rgba(255,255,255,0.10);
            margin-bottom: 18px;
        }

        .sidebar-brand-title {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0;
        }

        .sidebar-brand-subtitle {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.55);
            margin-top: 3px;
        }

        .sidebar-section-title {
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.45);
            margin: 0 9px 9px;
        }

        .train-group {
            margin-bottom: 5px;
        }

        .train-name {
            width: 100%;
            border: 0;
            background: transparent;
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 9px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .train-name:hover,
        .train-name.active {
            background: rgba(13,202,240,0.12);
        }

        .train-name .train-arrow {
            color: #0dcaf0;
            width: 13px;
            transition: transform 0.15s ease;
        }

        .train-name.collapsed .train-arrow {
            transform: rotate(-90deg);
        }

        .train-ids {
            padding: 3px 0 7px 31px;
        }

        .train-id {
            display: block;
            color: rgba(255,255,255,0.72);
            text-decoration: none;
            font-size: 0.74rem;
            padding: 6px 9px;
            border-left: 2px solid rgba(13,202,240,0.35);
            margin-bottom: 2px;
            border-radius: 0 6px 6px 0;
        }

        .train-id:hover,
        .train-id.active {
            color: #fff;
            background: rgba(255,255,255,0.07);
            border-left-color: #0dcaf0;
        }

        .sidebar-status {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #28a745;
            margin-left: auto;
            box-shadow: 0 0 5px rgba(40,167,69,0.65);
        }

        .sidebar-note {
            margin: 16px 6px 0;
            padding: 9px 10px;
            border-radius: 8px;
            background: rgba(0,0,0,0.12);
            color: rgba(255,255,255,0.45);
            font-size: 0.62rem;
            line-height: 1.45;
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1100;
            border: 1px solid rgba(255,255,255,0.18);
            background: #102b5c;
            color: #fff;
            border-radius: 8px;
            width: 40px;
            height: 40px;
        }

        @media (min-width: 769px) {
            body {
                padding-left: 245px !important;
            }
        }

        @media (max-width: 768px) {
            .railmap-sidebar {
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }

            .railmap-sidebar.show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: block;
            }
        }

    </style>
</head>
<body class="p-2 p-md-3">

    <!-- ===== SIDEBAR RAILMAP: TAMBAHAN SAJA ===== -->
    <button class="sidebar-toggle" type="button" onclick="toggleRailmapSidebar()" aria-label="Buka menu">
        <i class="bi bi-list"></i>
    </button>

    <aside class="railmap-sidebar" id="railmapSidebar">
        <div class="sidebar-brand">
            <h2 class="sidebar-brand-title">RAILMAP</h2>
            <div class="sidebar-brand-subtitle">Real-Time Train Monitoring</div>
        </div>

        <div class="sidebar-section-title">MONITORING KERETA</div>

        <!-- Nama kereta -->
        <div class="train-group">
            <button class="train-name active" type="button"
                    onclick="toggleTrainGroup('argo-wilis-ids', this)">
                <i class="bi bi-chevron-down train-arrow"></i>
                <i class="bi bi-train-front text-info"></i>
                <span>Argo Wilis</span>
                <span class="sidebar-status"></span>
            </button>

            <!--
                ID KERETA / RANGKAIAN.
                Ganti AW001, AW002, dst. dengan ID asli milik Argo Wilis.
                Bagian ini hanya navigasi visual; logic monitoring asli tidak diubah.
            -->
            <div class="train-ids" id="argo-wilis-ids">
                <a href="#" class="train-id active">AW001</a>
                <a href="#" class="train-id">AW002</a>
                <a href="#" class="train-id">AW003</a>
            </div>
        </div>

        <div class="train-group">
            <button class="train-name collapsed" type="button"
                    onclick="toggleTrainGroup('argo-bromo-ids', this)">
                <i class="bi bi-chevron-down train-arrow"></i>
                <i class="bi bi-train-front text-info"></i>
                <span>Argo Bromo Anggrek</span>
                <span class="sidebar-status"></span>
            </button>
            <div class="train-ids" id="argo-bromo-ids" style="display:none;">
                <a href="#" class="train-id">AB001</a>
                <a href="#" class="train-id">AB002</a>
            </div>
        </div>

        <div class="train-group">
            <button class="train-name collapsed" type="button"
                    onclick="toggleTrainGroup('argo-semeru-ids', this)">
                <i class="bi bi-chevron-down train-arrow"></i>
                <i class="bi bi-train-front text-info"></i>
                <span>Argo Semeru</span>
                <span class="sidebar-status"></span>
            </button>
            <div class="train-ids" id="argo-semeru-ids" style="display:none;">
                <a href="#" class="train-id">AS001</a>
                <a href="#" class="train-id">AS002</a>
            </div>
        </div>

        <div class="train-group">
            <button class="train-name collapsed" type="button"
                    onclick="toggleTrainGroup('gajayana-ids', this)">
                <i class="bi bi-chevron-down train-arrow"></i>
                <i class="bi bi-train-front text-info"></i>
                <span>Gajayana</span>
                <span class="sidebar-status"></span>
            </button>
            <div class="train-ids" id="gajayana-ids" style="display:none;">
                <a href="#" class="train-id">GJ001</a>
                <a href="#" class="train-id">GJ002</a>
            </div>
        </div>

        <div class="train-group">
            <button class="train-name collapsed" type="button"
                    onclick="toggleTrainGroup('bima-ids', this)">
                <i class="bi bi-chevron-down train-arrow"></i>
                <i class="bi bi-train-front text-info"></i>
                <span>Bima</span>
                <span class="sidebar-status"></span>
            </button>
            <div class="train-ids" id="bima-ids" style="display:none;">
                <a href="#" class="train-id">BM001</a>
                <a href="#" class="train-id">BM002</a>
            </div>
        </div>

        <div class="sidebar-note">
            Pilih nama kereta, kemudian pilih ID kereta/rangkaian.
            Daftar gerbong tetap ditampilkan pada halaman monitoring utama.
        </div>
    </aside>


    <!-- Header Dashboard -->
    <div class="dashboard-header mb-3">
        <h1 class="dashboard-title">RAILMAP</h1>
        <p class="text-light opacity-75 small mb-2">Real-Time Train Monitoring System</p>
        
        <!-- Nama Kereta Diperbesar & Last update utama dihapus -->
        <div class="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-50 px-4 py-2 rounded-pill shadow-sm" style="font-size: 0.95rem;">
            <i class="bi bi-train-front text-info fs-5"></i>
            <span class="fw-bold tracking-wide">KERETA ARGO WILIS</span>
        </div>

        <!-- SEARCH BAR FILTER GERBONG -->
        <div class="search-container position-relative">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="searchCarInput" class="form-control form-control-sm search-input" placeholder="Cari nomor gerbong (misal: 102436)..." oninput="filterCars()">
        </div>
    </div>

    <!-- Layout Grid Kartu Gerbong -->
    <div class="container" style="max-width: 600px;">
        <div class="row g-3 justify-content-center" id="cars-grid">
            <!-- 6 Kartu Gerbong -->
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

                        <!-- TAMPILAN CATATAN TERAPLIKASI -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-light opacity-75 mb-1">
                                <i class="bi bi-journal-text me-1 text-warning"></i>Catatan Perangkat
                            </label>
                            
                            <!-- Box Read-Only Samakan dengan Warna Tabel Detail -->
                            <div class="mb-2">
                                <div class="saved-notes-display" id="modalDisplayNotes">
                                    <em class="opacity-50">Belum ada catatan tersimpan.</em>
                                </div>
                            </div>

                            <!-- Header Input Baru + Tombol Clear -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-light opacity-75" style="font-size: 0.72rem;">
                                    <i class="bi bi-pencil-square me-1 text-info"></i>Isi Catatan Baru:
                                </span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.68rem;" onclick="clearNotesInput()" title="Hapus teks di kolom input">
                                    <i class="bi bi-trash me-1"></i>Clear Input
                                </button>
                            </div>

                            <!-- Textarea Input Catatan Baru dengan Border Highlight -->
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uniqueCars = ['K102436', 'K102438', 'K102437', 'K102439', 'M102411', 'K302452'];
        let globalDeviceData = [];
        
        const deviceModalElem = document.getElementById('deviceModal');
        const deviceModal = new bootstrap.Modal(deviceModalElem);

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
                <div class="col-12 col-sm-6 d-flex justify-content-center car-wrapper" data-car-id="${car}">
                    <div class="car-card">
                        <div class="car-header">
                            <span><i class="bi bi-distribute-vertical me-1 text-info"></i>Gerbong ${car}</span>
                            <span class="badge-status bg-secondary" id="badge-${car}">NO DATA</span>
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

        function filterCars() {
            const inputVal = document.getElementById('searchCarInput').value.trim().toLowerCase();
            const carElements = document.querySelectorAll('.car-wrapper');

            carElements.forEach(el => {
                const carID = el.getAttribute('data-car-id').toLowerCase();
                const numericOnly = carID.replace(/^[a-z]+/, '');

                if (carID.includes(inputVal) || numericOnly.includes(inputVal)) {
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
                const timeElem = document.getElementById(`time-${car}`);
                const devices = globalDeviceData.filter(d => d.location === car);

                if (devices.length === 0) {
                    bodyElem.innerHTML = `<div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Tidak ada data</div>`;
                    if (badgeElem) {
                        badgeElem.className = 'badge-status bg-secondary';
                        badgeElem.innerText = 'NO DATA';
                    }
                    if (timeElem) timeElem.innerText = '-';
                    return;
                }

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

                    const effectiveTime = dev.image_updated_at ? dev.image_updated_at : dev.timestamp;
                    
                    if (effectiveTime) {
                        if (!latestTimestamp || new Date(effectiveTime) > new Date(latestTimestamp)) {
                            latestTimestamp = effectiveTime;
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
            fetch('api_detail_status.php?trainset=Argo%20Wilis')
                .then(res => res.json())
                .then(data => {
                    globalDeviceData = data;
                    renderAllCars();
                })
                .catch(err => console.error("Error scan:", err));
        }

        scanData();
        setInterval(scanData, 1000);

        // ===== SIDEBAR RAILMAP: TAMBAHAN SAJA =====
        function toggleTrainGroup(id, button) {
            const group = document.getElementById(id);
            if (!group) return;

            const isHidden = group.style.display === 'none';

            // Tutup ID pada kereta lain agar sidebar tetap ringkas.
            document.querySelectorAll('.train-ids').forEach(el => {
                if (el !== group) el.style.display = 'none';
            });

            document.querySelectorAll('.train-name').forEach(el => {
                if (el !== button) el.classList.add('collapsed');
            });

            group.style.display = isHidden ? 'block' : 'none';
            button.classList.toggle('collapsed', !isHidden);
        }

        document.querySelectorAll('.train-id').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelectorAll('.train-id').forEach(el => el.classList.remove('active'));
                this.classList.add('active');

                // Saat ini hanya memilih ID pada sidebar.
                // Logic monitoring/API asli sengaja tidak diubah.
            });
        });

        function toggleRailmapSidebar() {
            document.getElementById('railmapSidebar').classList.toggle('show');
        }

    </script>
</body>
</html>
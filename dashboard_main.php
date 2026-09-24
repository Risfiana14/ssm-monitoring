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
        margin: 0;
        overflow-x: hidden;
    }

    /* Layout Wrapper Utama */
    .app-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    /* ---------------------------------------------------- */
    /* CSS SIDEBAR KIRI & ANIMASI TUTUPNYA                  */
    /* ---------------------------------------------------- */
    .railmap-sidebar {
        width: 260px;
        min-width: 260px;
        background-color: #122b59;
        border-right: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        padding: 15px;
        overflow-y: auto;
        z-index: 1050;
        transition: transform 0.3s ease-in-out;
        transform: translateX(0); /* Posisi awal terbuka */
    }

    /* Jika body ada class sidebar-closed, sidebar bergeser ke kiri (tertutup) di semua layar */
    body.sidebar-closed .railmap-sidebar {
        transform: translateX(-100%) !important;
    }

    /* Mengubah warna latar belakang tab saat aktif */
    .nav-pills .nav-link.active {
        background-color: #2890a7 !important; /* Ganti dengan kode warna yang diinginkan, misal hijau */
        color: #fff !important;
    }
    /* Mengubah warna teks tab saat tidak aktif */
    .nav-pills .nav-link {
        color: #adb5bd; 
    }

    .sidebar-brand {
        text-align: center;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 15px;
    }

    .sidebar-brand-title {
        font-weight: 800;
        font-size: 1.1rem;
        letter-spacing: 1px;
        margin: 0;
        color: #fff;
    }

    .sidebar-brand-subtitle {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .sidebar-section-title {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.5);
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
        background: rgba(13,202,240,0.15);
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
        color: rgba(255,255,255,0.75);
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
        background: rgba(255,255,255,0.08);
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
        background: rgba(0,0,0,0.15);
        color: rgba(255,255,255,0.5);
        font-size: 0.62rem;
        line-height: 1.45;
    }

    /* Tombol Toggle Sidebar */
    .sidebar-toggle-btn {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        border: 1px solid rgba(255,255,255,0.2);
        background: #122b59;
        color: #fff;
        border-radius: 8px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        cursor: pointer;
    }

    /* ---------------------------------------------------- */
    /* CSS KONTEN UTAMA KANAN & ATUR KOLOM (3 vs 4)         */
    /* ---------------------------------------------------- */
    .main-content {
        margin-left: 260px;
        flex: 1;
        width: calc(100% - 260px);
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    /* Default saat Sidebar Terbuka: 3 Kolom */
    .car-col-item {
        flex: 0 0 auto;
        width: 33.3333%; 
    }

    /* Jika Sidebar Ditutup: Margin kiri jadi 0, Kolom berubah jadi 4 (25%) */
    body.sidebar-closed .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    body.sidebar-closed .car-col-item {
        width: 25% !important; 
    }

    .dashboard-header {
        padding: 5px 0 10px 0;
        text-align: center;
    }

    .dashboard-title {
        font-weight: 800;
        letter-spacing: 2px;
        font-size: 2.1rem;
        margin-bottom: 2px;
    }

    /* ---------------------------------------------------- */
    /* KARTU KERETA & TOMBOL DEVICE KECIL & RAPI            */
    /* ---------------------------------------------------- */
    .car-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 10px 12px;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        width: 100%;
        margin-bottom: 15px;
    }

    .car-header {
        font-weight: 700;
        font-size: 0.82rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        gap: 6px;
    }

    .car-title-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        font-size: 0.8rem;
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

    .device-box.st-online { background-color: #28a745 !important; }
    .device-box.st-warning { background-color: #fd7e14 !important; }
    .device-box.st-offline { background-color: #dc3545 !important; }

    .badge-status {
        font-size: 0.55rem;
        padding: 2px 5px;
        border-radius: 8px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
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
    /* MEDIA QUERIES RESPONSIF (BERSIH & TANPA KONFLIK)    */
    /* ---------------------------------------------------- */
    @media (max-width: 1200px) {
        .car-col-item { width: 50% !important; } 
    }

    @media (max-width: 992px) {
        /* Sidebar defaultnya tersembunyi di luar layar kiri */
        .railmap-sidebar {
            transform: translateX(-100%) !important;
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1050;
            transition: transform 0.3s ease-in-out !important;
        }

        /* Saat class active ditambahkan, sidebar bergeser masuk ke dalam layar */
        .railmap-sidebar.active {
            transform: translateX(0) !important;
        }

        /* Konten utama di mobile menempati 100% lebar layar */
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding-top: 60px;
        }
    }

    @media (max-width: 575.98px) {
        .car-col-item { width: 100% !important; } 
        .car-card { padding: 8px !important; }
        .device-box { height: 26px !important; font-size: 0.5rem !important; }
    }
</style>
</head>
<body>

    <!-- Tombol Toggle Sidebar -->
    <button class="sidebar-toggle-btn" type="button" onclick="toggleRailmapSidebar()" aria-label="Buka menu">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="app-wrapper">
        <!-- SIDEBAR KIRI -->
        <aside class="railmap-sidebar" id="railmapSidebar">
            
            <div class="sidebar-brand">
                <h2 class="sidebar-brand-title">RAILMAP</h2>
                <div class="sidebar-brand-subtitle">Real-Time Train Monitoring</div>
            </div>

            <!-- Tombol Kembali ke Dashboard Utama (index.php) -->
            <div class="mb-3 text-center">
                <a href="index.php" class="btn btn-outline-info btn-sm d-inline-flex align-items-center justify-content-center gap-2 py-1 px-3" style="font-size: 0.7rem; font-weight: 600; border-radius: 6px;">
                    <i class="bi bi-arrow-left-circle" style="font-size: 0.8rem;"></i>
                    <span>Dashboard Utama</span>
                </a>
            </div>

            <!-- Header Manajemen Data dengan Ikon Create di Sebelahnya -->
            <div class="sidebar-section-title d-flex justify-content-between align-items-center" style="padding-right: 15px;">
                <span>MANAJEMEN DATA</span>
                <!-- Tombol ikon plus di samping tulisan manajemen data -->
                <button type="button" class="btn btn-sm p-0 text-info" data-bs-toggle="modal" data-bs-target="#modalCreateMenu" title="Tambah Data" style="background: none; border: none;">
                    <i class="bi bi-plus-circle-fill fs-6"></i>
                </button>
            </div>

            <!-- Menu Daftar Depo Dinamis -->
            <div class="train-group">
            <div class="train-ids" style="padding-left: 0px;">
            <?php
            // Menghubungkan ke database
            include 'db.php'; 
            
            try {
            // Mengambil semua data depo dari tabel depos
            $stmtDepo = $pdo->query("SELECT * FROM depos ORDER BY id ASC");
            $deposList = $stmtDepo->fetchAll(PDO::FETCH_ASSOC);

            if (count($deposList) > 0) {
                foreach ($deposList as $depo) {
                    $depoId = intval($depo['id']);
                    $collapseId = 'depoCollapse_' . $depoId;
                    $namaDepo = htmlspecialchars($depo['nama_depo']);
                    
                    echo '
                    <div class="mb-2 w-100">
                        <!-- Header Depo -->
    <div class="d-flex align-items-center w-100">

        <!-- Tombol Nama Depo -->
        <button
            class="train-name flex-grow-1 border-0 bg-transparent text-start d-flex align-items-center"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#' . $collapseId . '"
            aria-expanded="false"
            aria-controls="' . $collapseId . '">

            <i class="bi bi-chevron-down train-arrow me-2"></i>
            <i class="bi bi-folder text-info me-2"></i>
            <span class="text-light">' . $namaDepo . '</span>

        </button>

        <!-- Tombol Edit Depo -->
        <button
            type="button"
            class="btn btn-sm text-warning p-1 ms-1"
            data-bs-toggle="modal"
            data-bs-target="#modalEditDepo"
            data-depo-id="' . $depoId . '"
            data-depo-name="' . htmlspecialchars($depo['nama_depo'], ENT_QUOTES, 'UTF-8') . '"
            title="Edit nama depo">

            <i class="bi bi-pencil-square"></i>

        </button>

    </div>
                        
                        <!-- Area Dropdown untuk Nama Kereta (Level 2) dari tabel trains -->
                        <div class="collapse" id="' . $collapseId . '">
                            <div class="train-ids" style="padding-left: 25px;">';
                            
                            // Query untuk mengambil nama kereta berdasarkan depo_id
                            $stmtTrain = $pdo->prepare("SELECT * FROM trains WHERE depo_id = ? ORDER BY id ASC");
                            $stmtTrain->execute([$depoId]);
                            $trainsList = $stmtTrain->fetchAll(PDO::FETCH_ASSOC);

                            if (count($trainsList) > 0) {
                                foreach ($trainsList as $train) {
                                    $trainId = intval($train['id']); // Ambil ID kereta
                                    $namaKereta = htmlspecialchars($train['nama_kereta']);
                            ?>
                                    <!-- Ubah dari <span> menjadi <a> agar bisa diklik dan membawa parameter train_id -->
                                    <a href="dashboard_main.php?train_id=<?php echo $trainId; ?>" class="train-id d-block text-light text-decoration-none py-1 ps-2 rounded" style="font-size: 0.8rem;">
                                        <?php echo $namaKereta; ?>
                                    </a>
                            <?php
                                }
                            } else {
                                echo '<span class="train-id text-muted d-block" style="font-size: 0.7rem; font-style: italic;">Belum ada kereta</span>';
                            }

                    echo '
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="text-muted px-2" style="font-size: 0.75rem;">Belum ada depo</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="text-danger px-2" style="font-size: 0.75rem;">Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</div>

            
        </aside>

        <!-- KONTEN UTAMA KANAN -->
        <div class="main-content">
            <div class="dashboard-header mb-3">
                <h1 class="dashboard-title">RAILMAP</h1>
                
                <div class="d-inline-flex align-items-center gap-2 mb-2 my-2" style="font-size: 1rem;">
                    <i class="bi bi-train-front text-info fs-5"></i>
                    <span class="fw-bold tracking-wide">Real-Time Train Monitoring System</span>
                </div>
            </div>

            <!-- AREA KONTEN UTAMA (MENDUKUNG HALAMAN DETAIL KETIKA KERETA DIKLIK & TOMBOL CREATE Kereta) -->
            <div class="container-fluid px-2 px-md-3" style="max-width: 1600px;">
                <?php

                $selected_train_id = (int)($_GET['train_id'] ?? 0);

                if ($selected_train_id > 0) {

                    try {

                        /*
                        * Ambil data rangkaian berdasarkan train_id
                        */
                        $stmtTrain = $pdo->prepare("
                            SELECT 
                                t.*,
                                d.nama_depo
                            FROM trains t
                            LEFT JOIN depos d ON d.id = t.depo_id
                            WHERE t.id = ?
                            LIMIT 1
                        ");

                        $stmtTrain->execute([$selected_train_id]);

                        $currentTrain = $stmtTrain->fetch(PDO::FETCH_ASSOC);


                        if ($currentTrain) {

                            /*
                            * HEADER RANGKAIAN
                            */
                            echo '<div class="px-4 py-3 mb-4 d-flex justify-content-between align-items-center gap-3" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03)); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; backdrop-filter: blur(5px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);">';

                            echo '<div>';

                            echo '<h4 class="text-info mb-1 d-flex align-items-center gap-2" style="font-weight: 700; font-size: 1.25rem;">';
                            echo '<i class="bi bi-train-front"></i> Rangkaian Kereta: ' . htmlspecialchars($currentTrain['nama_kereta']);
                            echo '</h4>';

                            echo '<small class="text-light opacity-75" style="font-size: 0.8rem;">';
                            echo '<i class="bi bi-folder2-open me-1 text-warning"></i> Depo: ' . htmlspecialchars($currentTrain['nama_depo'] ?? '-');
                            echo '</small>';

                            echo '</div>';


                            /*
                            * TOMBOL TAMBAH NOMOR SARANA
                            */
                            echo '<button 
                                    type="button"
                                    class="btn btn-success btn-sm px-3 py-2 fw-bold"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAddLocation">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Tambah Nomor Sarana
                                </button>';

                            echo '</div>';


                            /*
                            * AMBIL NOMOR SARANA YANG SUDAH
                            * MASUK KE RANGKAIAN INI
                            */
                            $stmtCarriages = $pdo->prepare("
                                SELECT 
                                    id,
                                    location,
                                    internet_status
                                FROM carriages
                                WHERE train_id = ?
                                ORDER BY location ASC
                            ");

                            $stmtCarriages->execute([$selected_train_id]);

                            $carriagesList = $stmtCarriages->fetchAll(PDO::FETCH_ASSOC);


                            /*
                            * JIKA SUDAH ADA NOMOR SARANA
                            */
                            if (count($carriagesList) > 0) {
                            echo '<div class="row g-2 g-md-3 justify-content-center" id="train-carriages-grid">';

                            foreach ($carriagesList as $car) {
                                $location = htmlspecialchars($car['location'], ENT_QUOTES, 'UTF-8');
                                $internet = htmlspecialchars($car['internet_status'] ?? 'NO INTERNET', ENT_QUOTES, 'UTF-8');

                                echo '<div class="col-md-4 col-lg-3 car-wrapper" data-car-id="' . $location . '">';
                                echo '<div class="car-card">';
                                
                                // Header Kartu
                                echo '<div class="car-header">';
                                echo '<span class="car-title-text" title="' . $location . '">';
                                echo '<i class="bi bi-distribute-vertical me-1 text-info"></i>';
                                echo '<span>' . $location . '</span>';
                                echo '</span>';
                                
                                echo '<div class="d-flex align-items-center gap-1">';
                                // Badge Status Internet & Status Utama Kereta
                                echo '<span class="badge-status bg-secondary" id="internet-badge-' . $location . '">-</span>';
                                echo '<span class="badge-status bg-secondary" id="badge-' . $location . '">NO DATA</span>';
                                
                                // TOMBOL LEPAS DARI RANGKAIAN (Diperbesar agar kotak sempurna & sejajar)
                                echo '<form action="detach_carriage.php" method="POST" class="d-inline" onsubmit="return confirm(\'Lepas ' . $location . ' dari rangkaian ini?\')">';
                                echo '<input type="hidden" name="location" value="' . $location . '">';
                                echo '<input type="hidden" name="train_id" value="' . (int)$selected_train_id . '">';
                                echo '<button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center ms-1 p-0" style="width: 24px; height: 24px; font-size: 0.75rem; border-radius: 6px; line-height: 1;" title="Lepas dari Rangkaian">';
                                echo '<i class="bi bi-link-45deg"></i>';
                                echo '</button>';
                                echo '</form>';
                                
                                echo '</div>';
                                echo '</div>'; // End car-header

                                // Container Grid Perangkat (Diisi dinamis oleh JavaScript)
                                echo '<div class="device-grid-container" id="body-' . $location . '">';
                                echo '<div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Memuat...</div>';
                                echo '</div>';

                                // Footer Waktu Update
                                echo '<div class="text-center text-light opacity-75 mt-2 pt-1 border-top border-secondary border-opacity-25" style="font-size: 0.65rem;">';
                                echo '<i class="bi bi-clock me-1 text-warning"></i>Last update: <span id="time-' . $location . '">-</span>';
                                echo '</div>';

                                echo '</div>'; // End car-card
                                echo '</div>'; // End col
                            }

                            echo '</div>';
                            } else {

                                /*
                                * JIKA BELUM ADA NOMOR SARANA
                                */
                                echo '<div class="text-center py-5 text-light" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)); border: 1px dashed rgba(255, 255, 255, 0.25); border-radius: 12px; backdrop-filter: blur(5px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);">';
                                echo '<i class="bi bi-train-front display-4 text-warning mb-3 opacity-75"></i>';
                                echo '<h5 class="fw-bold mb-2">Belum ada nomor sarana pada rangkaian ini.</h5>';
                                echo '<p class="small text-light opacity-75 mb-3">';
                                echo 'Klik "Tambah Nomor Sarana" untuk memilih nomor sarana yang sudah terdeteksi monitoring.';
                                echo '</p>';

                                echo '<button 
                                        type="button"
                                        class="btn btn-success btn-sm mt-1 px-4 py-2 fw-bold shadow-sm"
                                        style="border-radius: 8px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAddLocation">
                                        <i class="bi bi-plus-circle-fill me-1"></i>
                                        Tambah Nomor Sarana
                                    </button>';

                                echo '</div>';
                            }

                        } else {

                            echo '<div class="alert alert-danger">';
                            echo 'Rangkaian kereta tidak ditemukan.';
                            echo '</div>';
                        }

                    } catch (PDOException $e) {

                        echo '<div class="alert alert-danger">';
                        echo 'Error: ' . htmlspecialchars($e->getMessage());
                        echo '</div>';
                    }

                } else {
                    // Tampilan default awal
                    echo '
                    <div style="width: 100%; text-align: center;">
                        <div style="display: inline-block; width: 520px; max-width: 95%;">
                            <div class="search-container position-relative d-flex align-items-center mb-0 px-3" style="width: 100%; background-color: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 50px; padding: 6px 12px; backdrop-filter: blur(5px);">
                                <select id="statusFilterDropdown" class="form-select form-select-sm bg-transparent text-light border-0 shadow-none" style="width: 140px; cursor: pointer; font-size: 0.85rem;" onchange="filterCars()">
                                    <option value="all" style="background-color: #1a233a; color: #fff;">Semua Status</option>
                                    <option value="online" style="background-color: #1a233a; color: #fff;">Online</option>
                                    <option value="warning" style="background-color: #1a233a; color: #fff;">Warning</option>
                                    <option value="offline" style="background-color: #1a233a; color: #fff;">Offline</option>
                                    <option value="internet" style="background-color: #1a233a; color: #fff;">Internet</option>
                                    <option value="no internet" style="background-color: #1a233a; color: #fff;">No Internet</option>
                                    <option value="no data" style="background-color: #1a233a; color: #fff;">No Data</option>
                                </select>
                                <div style="width: 1px; height: 20px; background-color: rgba(255, 255, 255, 0.2); margin: 0 8px; flex-shrink: 0;"></div>
                                <input type="text" id="searchCarInput" class="form-control form-control-sm border-0 bg-transparent text-light shadow-none ps-2" placeholder="Cari nomor kereta..." oninput="filterCars()" style="font-size: 0.85rem; box-shadow: none !important;">
                                <i class="bi bi-search text-light opacity-75 ps-2 pe-1" style="font-size: 0.85rem;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 g-md-3 justify-content-center mt-3" id="cars-grid">
                        <!-- Grid Kartu Kereta -->
                    </div>';
                }
                ?>
            </div>
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let uniqueCars = [];
        let globalDeviceData = [];
        let globalCarriagesData = [];
        
        function sortCarsByStatus() {
            uniqueCars.sort((a, b) => {
                let devA = globalDeviceData.filter(d => d.location === a);
                let devB = globalDeviceData.filter(d => d.location === b);

                let priorityA = 3, priorityB = 3;

                if (devA.length > 0) {
                    let hasOffA = devA.some(d => {
                        let st = (d.status || '').toUpperCase();
                        return st !== 'ONLINE' && st !== 'UP' && st !== 'WARNING';
                    });
                    let hasWarnA = devA.some(d => (d.status || '').toUpperCase() === 'WARNING');
                    if (hasOffA) priorityA = 1;
                    else if (hasWarnA) priorityA = 2;
                } else { priorityA = 4; }

                if (devB.length > 0) {
                    let hasOffB = devB.some(d => {
                        let st = (d.status || '').toUpperCase();
                        return st !== 'ONLINE' && st !== 'UP' && st !== 'WARNING';
                    });
                    let hasWarnB = devB.some(d => (d.status || '').toUpperCase() === 'WARNING');
                    if (hasOffB) priorityB = 1;
                    else if (hasWarnB) priorityB = 2;
                } else { priorityB = 4; }

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

        function filterCars() {
            const searchInput = document.getElementById('searchCarInput');
            const statusDropdown = document.getElementById('statusFilterDropdown');
            if (!searchInput || !statusDropdown) return;

            const inputVal = searchInput.value.trim().toLowerCase();
            const selectedStatus = statusDropdown.value.toLowerCase();
            const carElements = document.querySelectorAll('.car-wrapper');

            carElements.forEach(el => {
                const carID = el.getAttribute('data-car-id').toLowerCase();
                const numericOnly = carID.replace(/^[a-z]+/, '');
                const cardText = el.textContent.toLowerCase();

                const matchesSearch = carID.includes(inputVal) || numericOnly.includes(inputVal);

                let matchesStatus = true;
                if (selectedStatus === 'online') matchesStatus = cardText.includes('online');
                else if (selectedStatus === 'warning') matchesStatus = cardText.includes('warning');
                else if (selectedStatus === 'offline') matchesStatus = cardText.includes('offline');
                else if (selectedStatus === 'internet') matchesStatus = cardText.includes('internet') && !cardText.includes('no internet');
                else if (selectedStatus === 'no internet') matchesStatus = cardText.includes('no internet');
                else if (selectedStatus === 'no data') matchesStatus = cardText.includes('no data');

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
            
            // Jika berada di dashboard utama, render ulang kerangka grid utama
            if (gridContainer) {
                gridContainer.innerHTML = '';
                uniqueCars.forEach(car => {
                    gridContainer.innerHTML += `
                        <div class="car-col-item d-flex justify-content-center car-wrapper" data-car-id="${car}">
                            <div class="car-card">
                                <div class="car-header">
                                    <span class="car-title-text" title="${car}">
                                        <i class="bi bi-distribute-vertical me-1 text-info"></i>
                                        <span>${car}</span>
                                    </span>
                                    
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge-status bg-secondary" id="internet-badge-${car}">-</span>
                                        <span class="badge-status bg-secondary" id="badge-${car}">NO DATA</span>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 ms-1"
                                                style="font-size:0.65rem; line-height:1;"
                                                onclick="deleteCar('${car}')" title="Hapus kereta ini dari dashboard">
                                            <i class="bi bi-trash"></i>
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
            }

            // Ambil daftar kereta baik dari uniqueCars (dashboard utama) maupun dari elemen HTML halaman detail
            let targetCars = [];
            if (gridContainer) {
                targetCars = uniqueCars;
            } else {
                document.querySelectorAll('.car-wrapper').forEach(el => {
                    let carId = el.getAttribute('data-car-id');
                    if (carId) targetCars.push(carId);
                });
            }

            targetCars.forEach(car => {
                const bodyElem = document.getElementById(`body-${car}`);
                const badgeElem = document.getElementById(`badge-${car}`);
                const netBadgeElem = document.getElementById(`internet-badge-${car}`);
                const timeElem = document.getElementById(`time-${car}`);
                
                let devices = globalDeviceData.filter(d => d.location === car);

                if (devices.length === 0) {
                    if (bodyElem) bodyElem.innerHTML = `<div class="text-center text-light opacity-50 py-2 small" style="grid-column: span 5;">Tidak ada data</div>`;
                    if (badgeElem) { badgeElem.className = 'badge-status bg-secondary'; badgeElem.innerText = 'NO DATA'; }
                    if (netBadgeElem) { netBadgeElem.className = 'badge-status bg-secondary'; netBadgeElem.innerText = 'NO INTERNET'; }
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

                    if (st === 'ONLINE' || st === 'UP') stClass = 'st-online';
                    else if (st === 'WARNING') { stClass = 'st-warning'; hasWarning = true; }
                    else { hasOffline = true; }

                    const devTime = dev.image_updated_at || dev.timestamp;
                    if (devTime && (!latestTimestamp || new Date(devTime) > new Date(latestTimestamp))) {
                        latestTimestamp = devTime;
                    }

                    const shortLabel = getShortName(dev.device_name);

                    carHTML += `
                        <button class="device-box ${stClass}" onclick="showDeviceDetailByLocationAndIP('${dev.location}', '${dev.device_ip}')" title="${dev.device_name} (${dev.device_ip})">
                            ${shortLabel}
                        </button>
                    `;
                });

                if (bodyElem) bodyElem.innerHTML = carHTML;
                if (timeElem) timeElem.innerText = latestTimestamp ? latestTimestamp : '-';

                let carData = globalCarriagesData.find(c => c.location === car) || {};
                const carriageInternet = (carData.internet_status || 'NO_INTERNET').toUpperCase();
                
                let isInternetConnected = false;
                if (carriageInternet === 'INTERNET' && carData.last_timestamp) {
                    let diffSeconds = (new Date().getTime() - new Date(carData.last_timestamp).getTime()) / 1000;
                    if (diffSeconds < 120) isInternetConnected = true;
                }

                if (netBadgeElem) {
                    netBadgeElem.className = 'badge-status ' + (isInternetConnected ? 'bg-info text-dark' : 'bg-danger');
                    netBadgeElem.innerText = isInternetConnected ? 'INTERNET' : 'NO INTERNET';
                }

                if (badgeElem) {
                    badgeElem.className = 'badge-status ' + (hasOffline ? 'bg-danger' : (hasWarning ? 'bg-warning text-dark' : 'bg-success'));
                    badgeElem.innerText = hasOffline ? 'OFFLINE' : (hasWarning ? 'WARNING' : 'ONLINE');
                }
            });

            if (typeof filterCars === 'function') {
                filterCars();
            }
        }
        
        function scanData() {
            fetch('api_detail_status.php?trainset=DAOP_8')
                .then(res => res.json())
                .then(data => {
                    globalDeviceData = data;
                    sortCarsByStatus();
                    renderAllCars();
                })
                .catch(err => console.error("Error scan:", err));
        }

        function loadCarList() {
            return fetch('get_cars.php')
                .then(res => res.json())
                .then(cars => {
                    globalCarriagesData = cars; 
                    uniqueCars = cars.map(item => item.location);
                });
        }

        function deleteCar(car) {
            if (!confirm(`Yakin ingin menghapus kereta ${car} dari dashboard?`)) return;

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
            });
        }

        // =====================================================
// MELEPAS NOMOR SARANA DARI RANGKAIAN
// =====================================================
function detachCarriage(location) {

    console.log('detachCarriage dipanggil:', location);

    if (!confirm('Lepas ' + location + ' dari rangkaian ini?')) {
        return;
    }

    fetch('detach_carriage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'location=' + encodeURIComponent(location)
    })
    .then(function(response) {

        console.log('HTTP Status:', response.status);

        return response.text();
    })
    .then(function(text) {

        console.log('Response dari detach_carriage.php:', text);

        let result;

        try {
            result = JSON.parse(text);
        } catch (error) {
            console.error('Response bukan JSON:', text);
            alert('Server mengembalikan response yang tidak valid.');
            return;
        }

        if (result.status === 'ok') {

            alert(result.message);

            // refresh halaman supaya nomor sarana langsung hilang
            window.location.reload();

        } else {

            alert(result.message || 'Gagal melepas nomor sarana.');
        }
    })
    .catch(function(error) {

        console.error('FETCH ERROR:', error);

        alert(
            'Tidak dapat terhubung ke detach_carriage.php.\n' +
            'Periksa file detach_carriage.php.'
        );
    });
}

        function toggleTrainGroup(id, button) {
            const group = document.getElementById(id);
            if (!group) return;
            const isHidden = group.style.display === 'none';

            document.querySelectorAll('.train-ids').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.train-name').forEach(el => el.classList.add('collapsed'));

            group.style.display = isHidden ? 'block' : 'none';
            button.classList.toggle('collapsed', !isHidden);
        }

        document.querySelectorAll('.train-id').forEach(item => {
            item.addEventListener('click', function (e) {
                document.querySelectorAll('.train-id').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // JAVASCRIPT EDIT DEPO
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('[data-bs-target="#modalEditDepo"]').forEach(btn => {

                btn.addEventListener('click', function () {

                    document.getElementById('editDepoId').value =
                        this.dataset.depoId;

                    document.getElementById('editDepoName').value =
                        this.dataset.depoName;

                });

            });

        });

        // JAVASCRIPT TAMBAH NOMOR SARANA KE RANGKAIAN
        document.addEventListener('DOMContentLoaded', function () {

            const formAddLocation = document.getElementById('formAddLocation');

            if (!formAddLocation) {
                return;
            }

            formAddLocation.addEventListener('submit', function (e) {

                e.preventDefault();

                const formData = new FormData(this);

                fetch('assign_carriage.php', {
                    method: 'POST',
                    body: formData
                })

                .then(res => res.json())

                .then(result => {

                    if (result.status === 'ok') {

                        // Tutup modal
                        const modalElement = document.getElementById('modalAddLocation');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);

                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        // Muat ulang halaman agar nomor sarana langsung tampil
                        window.location.reload();

                    } else {

                        alert(
                            result.message ||
                            'Gagal menambahkan nomor sarana.'
                        );

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    alert(
                        'Terjadi kesalahan saat menambahkan nomor sarana.'
                    );

                });

            });

        });

        function toggleRailmapSidebar() {
            const sidebar = document.getElementById('railmapSidebar');
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('active');
            } else {
                document.body.classList.toggle('sidebar-closed');
            }
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('railmapSidebar');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');
            
            if (window.innerWidth <= 992) {
                if (sidebar && toggleBtn) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target) && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        document.body.classList.add('sidebar-closed');
                    }
                }
            }
        });

        loadCarList().then(() => {
            scanData();
            setInterval(scanData, 1000);
        });

        setInterval(() => loadCarList(), 5000);
    </script>

    <!-- Modal Pop-up Create (Depo & Nama Kereta) -->
    <div class="modal fade" id="modalCreateMenu" tabindex="-1" aria-labelledby="modalCreateMenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: #1a233a; color: #fff; border: 1px solid rgba(255,255,255,0.15);">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title" id="modalCreateMenuLabel" style="font-size: 0.95rem; font-weight: 700;">
                        <i class="bi bi-folder-plus text-info me-1"></i> Form Pembuatan Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <ul class="nav nav-pills nav-fill mb-3" id="createTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sm" id="depo-tab" data-bs-toggle="tab" data-bs-target="#depoTabContent" type="button" role="tab" style="font-size: 0.8rem; font-weight: 600;">Create Depo</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm" id="kereta-tab" data-bs-toggle="tab" data-bs-target="#keretaTabContent" type="button" role="tab" style="font-size: 0.8rem; font-weight: 600;">Create Nama Kereta</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="createTabContent">
                        <div class="tab-pane fade show active" id="depoTabContent" role="tabpanel">
                            <form action="create_depo.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.8rem;">Nama Depo</label>
                                    <input type="text" name="nama_depo" class="form-control form-control-sm text-light bg-dark border-secondary" placeholder="Contoh: Depo Induk Gambir" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-sm text-white fw-bold px-3">Simpan Depo</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="keretaTabContent" role="tabpanel">
                            <form action="create_kereta.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.8rem;">Pilih Depo</label>
                                    <select name="depo_id" class="form-select form-select-sm text-light bg-dark border-secondary" required>
                                        <option value="">-- Pilih Depo --</option>
                                        <?php
                                        try {
                                            $stmt =$pdo->query("SELECT * FROM depos ORDER BY nama_depo ASC");
                                            while ($row =$stmt->fetch()) {
                                                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nama_depo']) . '</option>';
                                            }
                                        } catch (PDOException $e) {
                                            echo '<option value="">Gagal memuat depo</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.8rem;">Nama Rangkaian Kereta</label>
                                    <input type="text" name="nama_kereta" class="form-control form-control-sm text-light bg-dark border-secondary" placeholder="Contoh: K102440" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-info btn-sm text-white fw-bold px-3">Simpan Kereta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Contoh untuk Tombol Create Kereta (Opsional / Siap Pakai) -->
    <div class="modal fade" id="modalAddLocation" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content"
             style="background-color:#1a233a;color:#fff;">

            <div class="modal-header border-bottom border-secondary">

                <h5 class="modal-title">
                    Tambah Nomor Sarana ke Rangkaian
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form id="formAddLocation">
                <div class="modal-body">
                    <!-- ID RANGKAIAN YANG SEDANG DIPILIH -->
                    <input
                        type="hidden"
                        name="train_id"
                        value="<?php echo (int)($selected_train_id ?? 0); ?>">

                    <div class="mb-3">
                        <label class="form-label small">
                            Nomor Sarana yang tersedia
                        </label>

                        <select
                            name="location"
                            class="form-select form-select-sm"
                            required>

                            <option value="">
                                -- Pilih Nomor Sarana --
                            </option>

                            <?php

                            if (!empty($selected_train_id)) {

                                /*
                                 * Hanya mengambil nomor sarana yang sudah ada di monitoring_logs dan belum memiliki train_id.
                                 */
                                $stmtAvailable = $pdo->prepare("
                                    SELECT DISTINCT
                                        ml.location

                                    FROM monitoring_logs AS ml

                                    LEFT JOIN carriages AS c
                                        ON c.location = ml.location

                                    WHERE ml.location IS NOT NULL
                                      AND TRIM(ml.location) <> ''
                                      AND c.train_id IS NULL

                                    ORDER BY ml.location ASC
                                ");

                                $stmtAvailable->execute();


                                while (
                                    $loc = $stmtAvailable->fetch(
                                        PDO::FETCH_ASSOC
                                    )
                                ) {

                                    echo '<option value="' .
                                        htmlspecialchars(
                                            $loc['location'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) .
                                        '">' .
                                        htmlspecialchars(
                                            $loc['location']
                                        ) .
                                        '</option>';
                                }
                            }

                            ?>

                        </select>


                        <div class="form-text text-secondary">

                            Hanya nomor sarana yang sudah terdeteksi
                            oleh monitoring dan belum masuk rangkaian lain.

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">

                        Batal

                    </button>


                    <button
                        type="submit"
                        class="btn btn-success btn-sm">

                        <i class="bi bi-link-45deg me-1"></i>

                        Masukkan ke Rangkaian

                    </button>
                </div>

            </form>

        </div>

    </div>

</div>
<!-- Modal Edit Depo -->
<div class="modal fade" id="modalEditDepo" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content"
             style="background-color:#1a233a;color:#fff;">

            <div class="modal-header border-bottom border-secondary">

                <h5 class="modal-title">
                    <i class="bi bi-pencil-square text-warning me-1"></i>
                    Edit Nama Depo
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <form action="edit_depo.php" method="POST">

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="depo_id"
                        id="editDepoId">


                    <div class="mb-3">

                        <label class="form-label small">
                            Nama Depo
                        </label>

                        <input
                            type="text"
                            name="nama_depo"
                            id="editDepoName"
                            class="form-control form-control-sm text-light bg-dark border-secondary"
                            required>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning btn-sm fw-bold">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
</body>
</html>
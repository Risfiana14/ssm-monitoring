<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - RAILMAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #163673; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
    </style>
</head>
<body class="p-4">
    <div class="container" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-2">
            <h4><i class="bi bi-bell-fill text-warning me-2"></i>Pusat Notifikasi Gangguan Sistem</h4>
            <div>
                <button onclick="clearNotifications()" class="btn btn-sm btn-outline-danger me-2"><i class="bi bi-trash"></i> Bersihkan Log</button>
                <a href="index.php" class="btn btn-sm btn-outline-light">Kembali ke Dashboard</a>
            </div>
        </div>
        
        <div id="notification-list" class="d-flex flex-column gap-2">
            <!-- Daftar notifikasi dimuat otomatis via JavaScript -->
        </div>
    </div>

    <script>
        function loadNotifications() {
            const container = document.getElementById('notification-list');
            let notifications = JSON.parse(localStorage.getItem('railmap_notifications') || '[]');

            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="card bg-dark text-light border-secondary">
                        <div class="card-body text-center py-4">
                            <p class="mb-0 text-muted">Belum ada pemberitahuan atau peringatan gangguan sistem saat ini.</p>
                        </div>
                    </div>`;
                return;
            }

            let html = '';
            notifications.forEach(n => {
                // Pastikan mengambil dari n.time, jika kosong cetak string kosong atau "-"
                let displayTime = (n && n.time) ? n.time : '-';
                let displayDate = (n && n.date) ? n.date : '';

                html += `
                    <div class="card bg-dark text-light border-danger border-opacity-50 shadow-sm mb-2">
                        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-danger mb-1">PERINGATAN</span>
                                <p class="mb-0 small fw-bold">${n.message || 'Pemberitahuan gangguan sistem'}</p>
                            </div>
                            <div class="text-end text-muted ps-3" style="font-size: 0.75rem; white-space: nowrap; border-left: 1px solid rgba(255,255,255,0.1);">
                                <i class="bi bi-clock me-1 text-warning"></i>${displayTime}
                                <div style="font-size: 0.65rem;" class="opacity-75">${displayDate}</div>
                            </div>
                        </div>
                    </div>`;
            });
            container.innerHTML = html;
        }

        function clearNotifications() {
            // Menghapus data memori browser sekaligus
            localStorage.removeItem('railmap_notifications');
            loadNotifications();
        }

        loadNotifications();
    </script>
</body>
</html>
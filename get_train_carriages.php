<?php
// Hubungkan ke database
include 'db.php';

// Tangkap train_id yang dikirim dari URL sidebar (?train_id=...)
$selected_train_id = $_GET['train_id'] ?? null;

if ($selected_train_id) {
    try {
        // 1. Ambil informasi nama kereta (Level 2) yang sedang diklik
        $stmtTrain = $pdo->prepare("SELECT * FROM trains WHERE id = ?");
        $stmtTrain->execute([$selected_train_id]);
        $currentTrain = $stmtTrain->fetch(PDO::FETCH_ASSOC);

        if ($currentTrain) {
            echo '<div class="p-3 mb-3 bg-dark border border-secondary rounded text-light">';
            echo '<h5 class="text-info mb-1">Rangkaian Kereta: ' . htmlspecialchars($currentTrain['nama_kereta']) . '</h5>';
            echo '<small class="text-muted">Depo ID: ' . $currentTrain['depo_id'] . '</small>';
            echo '</div>';

            // 2. Ambil data gerbong (carriages) yang sudah di-assign ke train_id ini
            // (Catatan: Pastikan tabel carriages Anda memiliki kolom train_id)
            $stmtCarriages = $pdo->prepare("SELECT * FROM carriages WHERE train_id = ?");
            $stmtCarriages->execute([$selected_train_id]);
            $carriagesList = $stmtCarriages->fetchAll(PDO::FETCH_ASSOC);

            if (count($carriagesList) > 0) {
                echo '<div class="row">';
                foreach ($carriagesList as $car) {
                    echo '
                    <div class="col-md-4 mb-3">
                        <div class="card bg-dark text-light border-secondary p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-warning">' . htmlspecialchars($car['location']) . '</h6>
                                <span class="badge bg-secondary">' . htmlspecialchars($car['internet_status']) . '</span>
                            </div>
                            <hr class="border-secondary my-2">
                            <small class="text-muted">Device Mikrotik terhubung</small>
                        </div>
                    </div>';
                }
                echo '</div>';
            } else {
                // Kondisi sesuai permintaan: Ketika baru dibuat, isinya masih kosong
                echo '<div class="alert alert-warning text-dark fst-italic py-3" style="font-size: 0.9rem;">';
                echo 'Belum ada unit gerbong yang dimasukkan ke kereta ini. Silakan gunakan tombol <strong>Add Location</strong> untuk mengambil unit dari dashboard utama.';
                echo '</div>';
            }

        } else {
            echo '<div class="alert alert-danger">Data kereta tidak ditemukan di database.</div>';
        }

    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error database: ' . $e->getMessage() . '</div>';
    }
} else {
    echo '<div class="text-muted">Pilih salah satu nama kereta di sidebar untuk melihat detail gerbong.</div>';
}
?>
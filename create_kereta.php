<?php
// File penampung untuk memproses form create kereta menggunakan PDO
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depo_id = trim($_POST['depo_id'] ?? '');
    $nama_kereta = trim($_POST['nama_kereta'] ?? '');

    if (!empty($depo_id) && !empty($nama_kereta)) {
        try {
            // Gunakan prepared statement PDO untuk memasukkan data kereta baru
            $stmt = $pdo->prepare("INSERT INTO carriages (location, depo_id, internet_status) VALUES (:location, :depo_id, 'NO_INTERNET')");
            $stmt->execute([
                'location' => $nama_kereta,
                'depo_id' => $depo_id
            ]);

            // Jika berhasil, arahkan kembali ke dashboard
            header("Location: dashboard_main.php?status=sukses_tambah_kereta");
            exit();

        } catch (PDOException $e) {
            echo "Gagal menyimpan data ke database: " . $e->getMessage();
        }
    } else {
        echo "Data tidak boleh kosong!";
    }
} else {
    header("Location: dashboard_main.php");
    exit();
}
?>
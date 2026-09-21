<?php
// File penampung untuk memproses form create depo menggunakan PDO
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_depo = trim($_POST['nama_depo'] ?? '');

    if (!empty($nama_depo)) {
        try {
            // Gunakan prepared statement PDO untuk keamanan dan mencegah error
            $stmt = $pdo->prepare("INSERT INTO depos (nama_depo) VALUES (:nama_depo)");
            $stmt->execute(['nama_depo' => $nama_depo]);

            // Jika berhasil disimpan, arahkan kembali ke dashboard
            header("Location: dashboard_main.php?status=sukses_tambah_depo");
            exit();
            
        } catch (PDOException $e) {
            echo "Gagal menyimpan depo ke database: " . $e->getMessage();
        }
    } else {
        echo "Nama depo tidak boleh kosong!";
    }
} else {
    header("Location: dashboard_main.php");
    exit();
}
?>
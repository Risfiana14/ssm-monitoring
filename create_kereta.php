<?php
// File yang bertugas menerima dan memproses data saat form tambah kereta disubmit
include 'koneksi.php'; // Sesuaikan nama file koneksi database Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depo_id = mysqli_real_escape_string($koneksi, $_POST['depo_id']);
    $nama_kereta = mysqli_real_escape_string($koneksi, trim($_POST['nama_kereta']));

    if (!empty($depo_id) && !empty($nama_kereta)) {
        // Masukkan data kereta baru ke tabel (sesuaikan nama kolom tabel database Anda, misal: location_code dan depo_id)
        $query = "INSERT INTO carriages (location_code, depo_id, internet_status) VALUES ('$nama_kereta', '$depo_id', 'NO_INTERNET')";
        
        if (mysqli_query($koneksi, $query)) {
            // Jika berhasil, arahkan kembali ke dashboard
            header("Location: dashboard_main.php?status=sukses_tambah_kereta");
            exit();
        } else {
            echo "Gagal menyimpan data ke database: " . mysqli_error($koneksi);
        }
    } else {
        echo "Data tidak boleh kosong!";
    }
} else {
    header("Location: dashboard_main.php");
    exit();
}
?>
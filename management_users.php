<?php
session_start();
require_once 'db.php';

// CEK AKSES SUPER ADMIN
if (($_SESSION['user_role'] ?? '') !== 'super_admin') {
    header('Location: dashboard_main.php');
    exit;
}

$error = '';
$success = '';

// PROSES AKSI MANAGEMENT USER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    try {

        // CREATE USER BARU
        if ($action === 'create_user') {

            $nama = trim($_POST['nama'] ?? '');
            $nipp = trim($_POST['nipp'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $depo = trim($_POST['depo'] ?? '');
            $status = $_POST['status'] ?? 'aktif';

            // Validasi data wajib
            if ($nama === '' || $nipp === '' || $password === '' || $depo === '') {
                throw new Exception('Semua data user wajib diisi.');
            }

            // Validasi password
            if (strlen($password) < 6) {
                throw new Exception('Password minimal 6 karakter.');
            }

            // Role yang diperbolehkan
            $allowedRoles = ['user', 'operator', 'admin'];

            if (!in_array($role, $allowedRoles, true)) {
                throw new Exception('Role tidak valid.');
            }

            // Depo yang diperbolehkan
            $allowedDepos = [
                'Depo Malang',
                'Depo Sidotopo',
                'Depo Surabaya Pasar Turi'
            ];

            if (!in_array($depo, $allowedDepos, true)) {
                throw new Exception('Depo tidak valid.');
            }

            // Status yang diperbolehkan
            $allowedStatus = [
                'aktif',
                'pending',
                'nonaktif'
            ];

            if (!in_array($status, $allowedStatus, true)) {
                throw new Exception('Status tidak valid.');
            }

            // Cek NIPP sudah terdaftar atau belum
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE nipp = ?
                LIMIT 1
            ");

            $stmt->execute([$nipp]);

            if ($stmt->fetch()) {
                throw new Exception('NIPP sudah terdaftar.');
            }

            // Hash password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Simpan user baru
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    nama,
                    nipp,
                    depo,
                    password,
                    role,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $nama,
                $nipp,
                $depo,
                $hashedPassword,
                $role,
                $status
            ]);

            $success = 'User baru berhasil dibuat.';

        // AKTIFKAN USER
        } elseif ($action === 'aktifkan') {

            if ($userId <= 0) {
                throw new Exception('User tidak valid.');
            }

            $stmt = $pdo->prepare("
                UPDATE users
                SET status = 'aktif'
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            $success = 'Akun berhasil diaktifkan.';

        // NONAKTIFKAN USER
        } elseif ($action === 'nonaktifkan') {

            if ($userId <= 0) {
                throw new Exception('User tidak valid.');
            }

            $stmt = $pdo->prepare("
                UPDATE users
                SET status = 'nonaktif'
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            $success = 'Akun berhasil dinonaktifkan.';

        // UBAH DATA USER
        } elseif ($action === 'update_user') {

            if ($userId <= 0) {
                throw new Exception('User tidak valid.');
            }

            $nama = trim($_POST['nama'] ?? '');
            $nipp = trim($_POST['nipp'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $depo = trim($_POST['depo'] ?? '');

            // Validasi data wajib
            if ($nama === '' || $nipp === '' || $depo === '') {
                throw new Exception('Nama, NIPP, dan Depo wajib diisi.');
            }

            // Role yang diperbolehkan
            $allowedRoles = ['user', 'operator', 'admin'];

            if (!in_array($role, $allowedRoles, true)) {
                throw new Exception('Role tidak valid.');
            }

            // Depo yang diperbolehkan
            $allowedDepos = [
                'Depo Malang',
                'Depo Sidotopo',
                'Depo Surabaya Pasar Turi'
            ];

            if (!in_array($depo, $allowedDepos, true)) {
                throw new Exception('Depo tidak valid.');
            }

            // Cek NIPP digunakan user lain atau tidak
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE nipp = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $nipp,
                $userId
            ]);

            if ($stmt->fetch()) {
                throw new Exception('NIPP sudah digunakan oleh user lain.');
            }

            // Update data user
            $stmt = $pdo->prepare("
                UPDATE users
                SET
                    nama = ?,
                    nipp = ?,
                    role = ?,
                    depo = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nama,
                $nipp,
                $role,
                $depo,
                $userId
            ]);

            $success = 'Data user berhasil diperbarui.';

        // HAPUS USER
        } elseif ($action === 'hapus') {

            if ($userId <= 0) {
                throw new Exception('User tidak valid.');
            }

            $stmt = $pdo->prepare("
                DELETE FROM users
                WHERE id = ?
            ");

            $stmt->execute([$userId]);

            $success = 'User berhasil dihapus.';

        } else {

            $error = 'Aksi tidak dikenali.';
        }

    } catch (PDOException $e) {

        $error = 'Terjadi kesalahan pada database.';

    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

// AMBIL DATA USER
try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama,
            nipp,
            depo,
            role,
            status
        FROM users
        ORDER BY
            CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'aktif' THEN 2
                ELSE 3
            END,
            id DESC
    ");

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $users = [];
    $error = 'Gagal mengambil data user.';
}

// HITUNG JUMLAH USER
$totalUsers = count($users);

$totalPending = 0;
$totalAktif = 0;
$totalNonaktif = 0;

foreach ($users as $user) {

    if ($user['status'] === 'pending') {
        $totalPending++;
    }

    if ($user['status'] === 'aktif') {
        $totalAktif++;
    }

    if ($user['status'] === 'nonaktif') {
        $totalNonaktif++;
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Management Users - RailMap</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        body {
            margin: 0;
            background: #163673;
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 30px;
            margin-left: 260px;
        }

        /* SIDEBAR RAILMAP */
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
            transform: translateX(0);
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
            color: rgba(255, 255, 255, 0.5);
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
            background: rgba(13, 202, 240, 0.15);
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
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 0.74rem;
            padding: 6px 9px;
            border-left: 2px solid rgba(13, 202, 240, 0.35);
            margin-bottom: 2px;
            border-radius: 0 6px 6px 0;
        }

        .train-id:hover,
        .train-id.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            border-left-color: #0dcaf0;
        }

        /* ACCOUNT */
        .sidebar-account {
            margin-top: auto;
            padding: 15px 12px 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar-account-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .sidebar-account-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(18, 200, 223, 0.12);
            border: 1px solid rgba(18, 200, 223, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #12c8df;
            flex-shrink: 0;
        }

        .sidebar-account-text {
            min-width: 0;
        }

        .sidebar-account-label {
            font-size: 10px;
            color: #7f9bc4;
            margin-bottom: 2px;
        }

        .sidebar-account-name {
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-logout {
            width: 100%;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid rgba(220, 53, 69, 0.7);
            border-radius: 7px;
            color: #ff5c6c;
            background: rgba(220, 53, 69, 0.06);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .sidebar-logout:hover {
            color: #ffffff;
            background: rgba(220, 53, 69, 0.18);
            border-color: #dc3545;
        }

        /* TOMBOL HAMBURGER */
        .sidebar-toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: #122b59;
            color: #fff;
            border-radius: 8px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* SIDEBAR TERTUTUP */
        body.sidebar-closed .railmap-sidebar {
            transform: translateX(-100%);
        }

        /* SAAT SIDEBAR DITUTUP */
        body.sidebar-closed .page-wrapper {
            margin-left: 0;
        }

        /* MOBILE */
        @media (max-width: 992px) {

            .railmap-sidebar {
                width: 260px;
                min-width: 260px;
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1050;
                transition: transform 0.3s ease-in-out;
                box-shadow: 8px 0 25px rgba(0, 0, 0, 0.25);
            }

            .railmap-sidebar.active {
                transform: translateX(0);
            }

            .page-wrapper {
                width: 100%;
                margin-left: 0 !important;
                padding-top: 70px;
            }

            .sidebar-toggle-btn {
                top: 12px;
                left: 8px;
                width: 46px;
                height: 46px;
                z-index: 1100;
            }

            .table-card {
                max-width: 100%;
            }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(13, 202, 240, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0dcaf0;
            font-size: 23px;
        }

        .page-title h1 {
            font-size: 25px;
            margin: 0;
            font-weight: 700;
        }

        .page-title p {
            margin: 4px 0 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }

        /* Stat Card */
        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            margin-bottom: 13px;
        }

        .stat-number {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
        }

        .icon-blue {
            background: rgba(13, 110, 253, 0.15);
            color: #4da3ff;
        }

        .icon-yellow {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        .icon-green {
            background: rgba(25, 135, 84, 0.15);
            color: #42d392;
        }

        .icon-red {
            background: rgba(220, 53, 69, 0.15);
            color: #ff6b7a;
        }

        /* Table Card */
        .table-card {
            background: #173b78;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            margin-top: 28px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .table-header {
            padding: 22px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #143565;
        }

        .table-header h2 {
            margin: 0;
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.1px;
        }

        .table-header h2 i {
            color: #0dcaf0;
        }

        .table-header span {
            display: block;
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }

        /* Container tabel */
        .table-responsive {
            overflow-x: auto;
            background: #21477f;
        }

        /* Tabel */
        .table {
            width: 100%;
            min-width: 1050px;
            margin: 0;
            color: #ffffff;
            vertical-align: middle;
            border-collapse: separate;
            border-spacing: 0;
        }

        /* Header tabel */
        .table thead th {
            background: #143565;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 18px;
            white-space: nowrap;
        }

        .table thead th:last-child {
            border-right: none;
        }

        /* Isi tabel */
        .table tbody td {
            background: #21477f;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 17px 18px;
            font-size: 14px;
            line-height: 1.5;
            white-space: nowrap;
        }

        .table tbody td:last-child {
            border-right: none;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Efek hover baris */
        .table tbody tr:hover td {
            background: #28538f;
            color: #ffffff;
        }

        /* Nomor */
        .table tbody td:first-child {
            width: 55px;
            text-align: center;
            color: #ffffff;
            font-weight: 600;
        }

        /* Nama */
        .table tbody td:nth-child(2) {
            min-width: 190px;
            color: #ffffff;
            font-weight: 600;
        }

        /* NIPP */
        .table tbody td:nth-child(3) {
            min-width: 130px;
            color: #ffffff;
            font-family: "Courier New", monospace;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Depo */
        .table tbody td:nth-child(4) {
            min-width: 210px;
            color: #ffffff;
        }

        /* Role dan Status */
        .table tbody td:nth-child(5),
        .table tbody td:nth-child(6) {
            min-width: 125px;
        }

        /* Kolom aksi */
        .table tbody td:last-child {
            min-width: 300px;
        }

        /* Badge Status dan Role */
        .badge-status,
        .badge-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 82px;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
        }

        /* STATUS PENDING */
        .badge-pending {
            background: rgba(255, 193, 7, 0.12);
            border-color: rgba(255, 193, 7, 0.28);
            color: #ffd45a;
        }

        /* STATUS AKTIF */
        .badge-aktif {
            background: rgba(25, 135, 84, 0.14);
            border-color: rgba(66, 211, 146, 0.28);
            color: #61e6a4;
        }

        /* STATUS NONAKTIF */
        .badge-nonaktif {
            background: rgba(220, 53, 69, 0.13);
            border-color: rgba(255, 107, 122, 0.28);
            color: #ff8792;
        }

        /* ROLE USER */
        .badge-user {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.25);
            color: #cbd5e1;
        }

        /* ROLE OPERATOR */
        .badge-operator {
            background: rgba(13, 202, 240, 0.12);
            border-color: rgba(13, 202, 240, 0.28);
            color: #4dd8f3;
        }

        /* ROLE ADMIN */
        .badge-admin {
            background: rgba(139, 92, 246, 0.14);
            border-color: rgba(167, 139, 250, 0.30);
            color: #c4a7ff;
        }

        /* Button */
        .action-buttons {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .btn-action {
            border: none;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-edit {
            background: rgba(13, 202, 240, 0.12);
            color: #0dcaf0;
        }

        .btn-edit:hover {
            background: rgba(13, 202, 240, 0.22);
        }

        .btn-active {
            background: rgba(25, 135, 84, 0.15);
            color: #42d392;
        }

        .btn-active:hover {
            background: rgba(25, 135, 84, 0.25);
        }

        .btn-deactivate {
            background: rgba(255, 193, 7, 0.12);
            color: #ffc107;
        }

        .btn-deactivate:hover {
            background: rgba(255, 193, 7, 0.22);
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.12);
            color: #ff6b7a;
        }

        .btn-delete:hover {
            background: rgba(220, 53, 69, 0.22);
        }

       /* Alert */
        .alert-custom {
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success-custom {
            background: rgba(25, 135, 84, 0.13);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #6fe3a8;
        }

        .alert-error-custom {
            background: rgba(220, 53, 69, 0.13);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8792;
        }

        /* Modal */
        .modal-content {
            background: #173b78;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-radius: 14px;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            font-size: 17px;
        }

        .btn-close {
            filter: invert(1);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            background: #0f172a;
            border-color: #0dcaf0;
            color: #ffffff;
            box-shadow: 0 0 0 0.15rem rgba(13, 202, 240, 0.12);
        }

        .form-select option {
            background: #173b78;
            color: #ffffff;
        }

        .btn-save {
            background: #0dcaf0;
            border: none;
            color: #031321;
            font-weight: 600;
            padding: 9px 17px;
            border-radius: 8px;
        }

        .btn-save:hover {
            background: #31d6f4;
        }

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
        }

        @media (max-width: 768px) {

        .page-wrapper {
            padding: 75px 18px 18px;
        }

        .page-header {
            align-items: flex-start;
            flex-direction: column;
            margin-bottom: 22px;
        }

        .page-title h1 {
            font-size: 21px;
        }

        .page-title p {
            font-size: 12px;
        }

        .page-title-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .table-card {
            margin-top: 20px;
        }

        .table-header {
            padding: 18px;
        }

        .table-header h2 {
            font-size: 16px;
        }

        .table-header span {
            font-size: 12px;
        }
    }

    @media (max-width: 576px) {

        .page-wrapper {
            padding: 72px 12px 15px;
        }

        .page-title {
            gap: 10px;
        }

        .page-title h1 {
            font-size: 19px;
        }

        .page-title p {
            font-size: 11px;
        }

        .stat-card {
            padding: 17px;
        }

        .stat-number {
            font-size: 24px;
        }

        .table-card {
            border-radius: 12px;
        }

        .table-header {
            padding: 16px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    </style>

</head>

<body>

<!-- TOGGLE SIDEBAR -->
<button
    class="sidebar-toggle-btn"
    type="button"
    onclick="toggleManagementSidebar()"
    aria-label="Buka menu"
>
    <i class="bi bi-list fs-5"></i>
</button>

<!-- SIDEBAR RAILMAP -->
<aside class="railmap-sidebar" id="railmapSidebar">

    <div class="sidebar-brand">
        <h2 class="sidebar-brand-title">
            RAILMAP
        </h2>

        <div class="sidebar-brand-subtitle">
            Real-Time Train Monitoring
        </div>
    </div>

    <!-- DASHBOARD UTAMA -->
    <div class="mb-3 text-center">

        <a
            href="index.php"
            class="btn btn-outline-info btn-sm d-inline-flex align-items-center justify-content-center gap-2 py-1 px-3"
            style="font-size: 0.7rem; font-weight: 600; border-radius: 6px;"
        >

            <i
                class="bi bi-arrow-left-circle"
                style="font-size: 0.8rem;"
            ></i>

            <span>
                Dashboard Utama
            </span>

        </a>

    </div>

    <!-- DASHBOARD MAIN -->
    <div
        class="sidebar-section-title d-flex justify-content-between align-items-center"
        style="padding-right: 15px;"
    >

        <span>
            <a
                href="dashboard_main.php"
                class="text-decoration-none text-light opacity-75"
                style="font-size: 0.65rem; font-weight: 700;"
            >
                DASHBOARD MAIN
            </a>
        </span>

        <!-- TOMBOL PLUS -->
        <button
            type="button"
            class="btn btn-sm p-0 text-info"
            style="background: none; border: none;"
            onclick="window.location.href='dashboard_main.php'"
            title="Tambah Data"
        >
            <i class="bi bi-plus-circle-fill fs-6"></i>
        </button>

    </div>

    <!-- DAFTAR DEPO -->
    <div class="train-group">

        <div
            class="train-ids"
            style="padding-left: 0px;"
        >

            <?php
            try {

                $stmtDepo = $pdo->query("
                    SELECT *
                    FROM depos
                    ORDER BY id ASC
                ");

                $deposList = $stmtDepo->fetchAll(PDO::FETCH_ASSOC);

                foreach ($deposList as $depo) {

                    $depoId = (int) $depo['id'];
                    $collapseId = 'depoCollapse_user_' . $depoId;
                    $namaDepo = htmlspecialchars(
                        $depo['nama_depo'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
            ?>

                <div class="mb-2 w-100">

                    <div class="d-flex align-items-center w-100">

                        <!-- Tombol Dropdown Nama Depo (Menggunakan button collapse, bukan link pindah halaman) -->
                        <button
                            class="train-name flex-grow-1 border-0 bg-transparent text-start d-flex align-items-center collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?php echo $collapseId; ?>"
                            aria-expanded="false"
                            aria-controls="<?php echo $collapseId; ?>"
                        >

                            <i class="bi bi-chevron-down train-arrow me-2"></i>

                            <i class="bi bi-folder text-info me-2"></i>

                            <span class="text-light">
                                <?php echo $namaDepo; ?>
                            </span>

                        </button>

                    </div>

                    <!-- DROPDOWN NAMA KERETA DI DALAM DEPO -->
                    <div
                        class="collapse"
                        id="<?php echo $collapseId; ?>"
                    >
                        <div
                            class="train-ids"
                            style="padding-left: 25px;"
                        >
                            <?php
                            // Ambil kereta berdasarkan depo
                            $stmtTrain = $pdo->prepare("
                                SELECT *
                                FROM trains
                                WHERE depo_id = ?
                                ORDER BY id ASC
                            ");
                            $stmtTrain->execute([$depoId]);
                            $trainsList = $stmtTrain->fetchAll(PDO::FETCH_ASSOC);

                            if (count($trainsList) > 0) {
                                foreach ($trainsList as $train) {
                                    $trainId = (int)$train['id'];
                                    $namaKereta = htmlspecialchars($train['nama_kereta'], ENT_QUOTES, 'UTF-8');
                            ?>
                                    <!-- NAMA KERETA (Hanya elemen ini yang menuju ke dashboard saat diklik) -->
                                    <a
                                        href="dashboard_main.php?train_id=<?php echo $trainId; ?>"
                                        class="train-id d-block text-light text-decoration-none py-1 ps-2 rounded mb-1"
                                        style="font-size: 0.8rem;"
                                    >
                                        <?php echo $namaKereta; ?>
                                    </a>
                            <?php
                                }
                            } else {
                            ?>
                                <span
                                    class="train-id text-muted d-block"
                                    style="font-size: 0.7rem; font-style: italic;"
                                >
                                    Belum ada kereta
                                </span>
                            <?php
                            }
                            ?>
                        </div>
                    </div>

                </div>

            <?php
                }

            } catch (PDOException $e) {
                // Tidak menampilkan error di sidebar
            }
            ?>

        </div>

    </div>

    <!-- LAPORAN PERANGKAT -->
    <div class="mt-3">

        <button
            type="button"
            class="train-name w-100 border-0 bg-transparent text-start d-flex align-items-center collapsed"
            data-bs-toggle="collapse"
            data-bs-target="#laporanPerangkatMenu"
            aria-expanded="false"
            aria-controls="laporanPerangkatMenu"
        >

            <i class="bi bi-chevron-down train-arrow me-2"></i>

            <i class="bi bi-file-earmark-text text-info me-2"></i>

            <span class="text-light">
                Laporan Perangkat
            </span>

        </button>

        <div
            class="collapse"
            id="laporanPerangkatMenu"
        >

            <div
                class="train-ids"
                style="padding-left: 25px;"
            >

                <a href="dashboard_main.php" class="train-id" onclick="sessionStorage.setItem('openTab', 'trouble');">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Laporan Perangkat Trouble
                </a>

                <a href="dashboard_main.php" class="train-id" onclick="sessionStorage.setItem('openTab', 'repair');">
                    <i class="bi bi-tools me-2 text-success"></i>
                    Laporan Perangkat Perbaikan
                </a>

            </div>

        </div>

    </div>

    <!-- ANALITIK -->
    <div class="mt-3">

        <button
            type="button"
            class="train-name w-100 border-0 bg-transparent text-start d-flex align-items-center collapsed"
            data-bs-toggle="collapse"
            data-bs-target="#analitikMenu"
            aria-expanded="false"
            aria-controls="analitikMenu"
        >

            <i class="bi bi-chevron-down train-arrow me-2"></i>

            <i class="bi bi-bar-chart-line text-info me-2"></i>

            <span class="text-light">
                Analitik
            </span>

        </button>

        <div
            class="collapse"
            id="analitikMenu"
        >

            <div
                class="train-ids"
                style="padding-left: 25px;"
            >

                <a href="dashboard_main.php" class="train-id" onclick="sessionStorage.setItem('openTab', 'analytics');">
                    <i class="bi bi-cpu me-2 text-info"></i>
                    Analitik Perangkat
                </a>

            </div>

        </div>

    </div>

    <!-- MANAGEMENT USERS -->
    <div class="mt-3">

        <a
            href="management_users.php"
            class="train-name active text-decoration-none"
        >

            <i class="bi bi-people-fill text-info me-2"></i>

            <span class="text-light">
                Management Users
            </span>

        </a>

    </div>

    <!-- ACCOUNT -->
    <div class="sidebar-account">

        <div class="sidebar-account-info">

            <div class="sidebar-account-icon">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="sidebar-account-text">

                <div class="sidebar-account-label">
                    Pengguna
                </div>

                <div class="sidebar-account-name">
                    <?= htmlspecialchars($_SESSION['user_nama'] ?? 'User') ?>
                </div>

            </div>

        </div>

        <a
            href="logout.php"
            class="sidebar-logout"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>

<div class="page-wrapper">

    <!-- Header -->
    <div class="page-header">
        <div class="page-title">
            <div class="page-title-icon">
                <i class="bi bi-people-fill"></i>
            </div>

            <div>
                <h1>Management Users</h1>
                <p>Kelola akun dan hak akses pengguna RailMap</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert-custom alert-success-custom">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-custom alert-error-custom">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Statistik -->
    <div class="row g-3">

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon icon-blue">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div class="stat-number">
                    <?= $totalUsers ?>
                </div>

                <div class="stat-label">
                    Total User
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon icon-yellow">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div class="stat-number">
                    <?= $totalPending ?>
                </div>

                <div class="stat-label">
                    Menunggu Aktivasi
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon icon-green">
                    <i class="bi bi-person-check-fill"></i>
                </div>

                <div class="stat-number">
                    <?= $totalAktif ?>
                </div>

                <div class="stat-label">
                    User Aktif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon icon-red">
                    <i class="bi bi-person-x-fill"></i>
                </div>

                <div class="stat-number">
                    <?= $totalNonaktif ?>
                </div>

                <div class="stat-label">
                    User Nonaktif
                </div>
            </div>
        </div>

    </div>

    <!-- TABLE USER -->
    <div class="table-card">

        <div class="table-header">

            <div>

                <h2>
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Daftar Pengguna
                </h2>

                <span>
                    Kelola akun, role, status, dan depo pengguna
                </span>

            </div>

            <!-- TOMBOL CREATE USER -->
            <button
                type="button"
                class="btn btn-info d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal"
                data-bs-target="#createUserModal"
                style="font-size: 13px; font-weight: 600; border-radius: 8px;"
            >
                <i class="bi bi-person-plus-fill"></i>
                Tambah User
            </button>

        </div>

        <div class="table-responsive">

            <?php if (empty($users)): ?>

                <div class="empty-state">

                    <i class="bi bi-people"></i>

                    Belum ada pengguna yang terdaftar.

                </div>

            <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIPP</th>
                            <th>Depo</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($users as $index => $user): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($user['nama']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['nipp']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['depo']) ?>
                            </td>

                            <!-- ROLE -->
                            <td>

                                <?php if ($user['role'] === 'admin'): ?>

                                    <span class="badge-role badge-admin">
                                        Admin
                                    </span>

                                <?php elseif ($user['role'] === 'operator'): ?>

                                    <span class="badge-role badge-operator">
                                        Operator
                                    </span>

                                <?php else: ?>

                                    <span class="badge-role badge-user">
                                        User
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- STATUS -->
                            <td>

                                <?php if ($user['status'] === 'aktif'): ?>

                                    <span class="badge-status badge-aktif">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Aktif
                                    </span>

                                <?php elseif ($user['status'] === 'pending'): ?>

                                    <span class="badge-status badge-pending">
                                        <i class="bi bi-clock me-1"></i>
                                        Pending
                                    </span>

                                <?php else: ?>

                                    <span class="badge-status badge-nonaktif">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Nonaktif
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- AKSI -->
                            <td>

                                <div class="action-buttons">

                                    <!-- EDIT ROLE + DEPO -->
                                    <button
                                        type="button"
                                        class="btn-action btn-edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-id="<?= $user['id'] ?>"
                                        data-nama="<?= htmlspecialchars($user['nama'], ENT_QUOTES) ?>"
                                        data-nipp="<?= htmlspecialchars($user['nipp'], ENT_QUOTES) ?>"
                                        data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES) ?>"
                                        data-depo="<?= htmlspecialchars($user['depo'], ENT_QUOTES) ?>"
                                    >
                                        <i class="bi bi-pencil-fill me-1"></i>
                                        Edit
                                    </button>

                                    <!-- AKTIFKAN -->
                                    <?php if ($user['status'] !== 'aktif'): ?>

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Aktifkan akun <?= htmlspecialchars($user['nama'], ENT_QUOTES) ?>?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="aktifkan"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= $user['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-action btn-active"
                                            >
                                                <i class="bi bi-check-lg me-1"></i>
                                                Aktifkan
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                    <!-- NONAKTIFKAN -->
                                    <?php if ($user['status'] === 'aktif'): ?>

                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Nonaktifkan akun <?= htmlspecialchars($user['nama'], ENT_QUOTES) ?>?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="nonaktifkan"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= $user['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-action btn-deactivate"
                                            >
                                                <i class="bi bi-pause-fill me-1"></i>
                                                Nonaktifkan
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                    <!-- HAPUS -->
                                    <form
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus akun <?= htmlspecialchars($user['nama'], ENT_QUOTES) ?>? Data user akan dihapus permanen.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="hapus"
                                        >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= $user['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-action btn-delete"
                                        >
                                            <i class="bi bi-trash-fill me-1"></i>
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- =========================================================
     MODAL CREATE USER
========================================================= -->

<div
    class="modal fade"
    id="createUserModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-person-plus-fill me-2"></i>

                        Tambah User Baru

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="action"
                        value="create_user"
                    >

                    <!-- NAMA -->
                    <div class="mb-3">

                        <label class="form-label">
                            Nama
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Masukkan nama lengkap"
                            required
                        >

                    </div>

                    <!-- NIPP -->
                    <div class="mb-3">

                        <label class="form-label">
                            NIPP
                        </label>

                        <input
                            type="text"
                            name="nipp"
                            class="form-control"
                            placeholder="Masukkan NIPP"
                            required
                        >

                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Minimal 6 karakter"
                            minlength="6"
                            required
                        >

                    </div>

                    <!-- ROLE -->
                    <div class="mb-3">

                        <label class="form-label">
                            Role
                        </label>

                        <select
                            name="role"
                            class="form-select"
                            required
                        >

                            <option value="user">
                                User
                            </option>

                            <option value="operator">
                                Operator
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                        </select>

                    </div>

                    <!-- DEPO -->
                    <div class="mb-3">

                        <label class="form-label">
                            Depo
                        </label>

                        <select
                            name="depo"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Pilih Depo
                            </option>

                            <option value="Depo Malang">
                                Depo Malang
                            </option>

                            <option value="Depo Sidotopo">
                                Depo Sidotopo
                            </option>

                            <option value="Depo Surabaya Pasar Turi">
                                Depo Surabaya Pasar Turi
                            </option>

                        </select>

                    </div>

                    <!-- STATUS -->
                    <div class="mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >

                            <option value="aktif">
                                Aktif
                            </option>

                            <option value="pending">
                                Pending
                            </option>

                            <option value="nonaktif">
                                Nonaktif
                            </option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        <i class="bi bi-person-plus-fill me-1"></i>
                        Buat User
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     MODAL EDIT USER
========================================================= -->

<div
    class="modal fade"
    id="editUserModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-person-gear me-2"></i>

                        Edit Hak Akses User

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="action"
                        value="update_user"
                    >

                    <input
                        type="hidden"
                        name="user_id"
                        id="edit_user_id"
                    >

                    <div class="mb-3">

                        <label class="form-label">
                            Nama
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            id="edit_nama"
                            placeholder="Masukkan nama lengkap"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            NIPP
                        </label>

                        <input
                            type="text"
                            name="nipp"
                            class="form-control"
                            id="edit_nipp"
                            placeholder="Masukkan NIPP"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Role
                        </label>

                        <select
                            name="role"
                            id="edit_role"
                            class="form-select"
                            required
                        >

                            <option value="user">
                                User
                            </option>

                            <option value="operator">
                                Operator
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Depo
                        </label>

                        <select
                            name="depo"
                            id="edit_depo"
                            class="form-select"
                            required
                        >

                            <option value="Depo Malang">
                                Depo Malang
                            </option>

                            <option value="Depo Sidotopo">
                                Depo Sidotopo
                            </option>

                            <option value="Depo Surabaya Pasar Turi">
                                Depo Surabaya Pasar Turi
                            </option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        <i class="bi bi-check-lg me-1"></i>
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const editModal = document.getElementById('editUserModal');

    if (editModal) {

        editModal.addEventListener('show.bs.modal', function (event) {

            const button = event.relatedTarget;

            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const nipp = button.getAttribute('data-nipp');
            const role = button.getAttribute('data-role');
            const depo = button.getAttribute('data-depo');

            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_nipp').value = nipp;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_depo').value = depo;

        });

    }

});


/* =========================================
   TOGGLE SIDEBAR MANAGEMENT USERS
========================================= */

function toggleManagementSidebar() {

    const sidebar = document.getElementById('railmapSidebar');

    if (!sidebar) {
        return;
    }

    if (window.innerWidth <= 992) {

        sidebar.classList.toggle('active');

    } else {

        document.body.classList.toggle('sidebar-closed');

    }

}


/* =========================================
   TUTUP SIDEBAR SAAT KLIK DI LUAR
   KHUSUS MOBILE
========================================= */

document.addEventListener('click', function (event) {

    if (window.innerWidth > 992) {
        return;
    }

    const sidebar = document.getElementById('railmapSidebar');
    const toggleButton = document.querySelector('.sidebar-toggle-btn');

    if (!sidebar || !toggleButton) {
        return;
    }

    if (
        !sidebar.contains(event.target) &&
        !toggleButton.contains(event.target) &&
        sidebar.classList.contains('active')
    ) {

        sidebar.classList.remove('active');

    }

});

</script>

</body>
</html>
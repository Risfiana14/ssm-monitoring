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

        // AKTIFKAN USER
        if ($action === 'aktifkan') {

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

        // UBAH ROLE DAN DEPO
        } elseif ($action === 'update_user') {

            if ($userId <= 0) {
                throw new Exception('User tidak valid.');
            }

            $role = $_POST['role'] ?? 'user';
            $depo = trim($_POST['depo'] ?? '');

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

            $stmt = $pdo->prepare("
                UPDATE users
                SET role = ?, depo = ?
                WHERE id = ?
            ");

            $stmt->execute([
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
            background: #071a35;
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 30px;
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
            color: #8da6c7;
            font-size: 13px;
        }
        .btn-back {
            background: #102d52;
            border: 1px solid #31527c;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-back:hover {
            background: #173a66;
            color: #ffffff;
        }

        /* Stat Card */
        .stat-card {
            background: #0b2445;
            border: 1px solid #1d3d65;
            border-radius: 14px;
            padding: 20px;
            height: 100%;
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
            color: #8da6c7;
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

        /* Table */
        .table-card {
            background: #0b2445;
            border: 1px solid #1d3d65;
            border-radius: 14px;
            margin-top: 25px;
            overflow: hidden;
        }
        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #1d3d65;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h2 {
            margin: 0;
            font-size: 17px;
        }
        .table-header span {
            color: #8da6c7;
            font-size: 13px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            margin: 0;
            color: #ffffff;
            vertical-align: middle;
            min-width: 900px;
        }
        .table thead th {
            background: #102d52;
            color: #9db5d4;
            border-bottom: 1px solid #284a72;
            font-size: 12px;
            font-weight: 600;
            padding: 14px 16px;
            white-space: nowrap;
        }
        .table tbody td {
            background: #0b2445;
            border-bottom: 1px solid #17395f;
            padding: 14px 16px;
            font-size: 13px;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover td {
            background: #102d52;
        }

        /* Badge */
        .badge-status,
        .badge-role {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-pending {
            background: rgba(255, 193, 7, 0.12);
            color: #ffc107;
        }
        .badge-aktif {
            background: rgba(25, 135, 84, 0.12);
            color: #42d392;
        }
        .badge-nonaktif {
            background: rgba(220, 53, 69, 0.12);
            color: #ff6b7a;
        }
        .badge-user {
            background: rgba(108, 117, 125, 0.18);
            color: #c9d1d9;
        }
        .badge-operator {
            background: rgba(13, 202, 240, 0.12);
            color: #0dcaf0;
        }
        .badge-admin {
            background: rgba(111, 66, 193, 0.16);
            color: #b99aff;
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
            background: #0b2445;
            border: 1px solid #31527c;
            color: #ffffff;
            border-radius: 14px;
        }
        .modal-header {
            border-bottom: 1px solid #1d3d65;
        }
        .modal-footer {
            border-top: 1px solid #1d3d65;
        }
        .modal-title {
            font-size: 17px;
        }
        .btn-close {
            filter: invert(1);
        }
        .form-label {
            color: #9db5d4;
            font-size: 13px;
            margin-bottom: 7px;
        }
        .form-control,
        .form-select {
            background: #081d38;
            border: 1px solid #31527c;
            color: #ffffff;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
        }
        .form-control:focus,
        .form-select:focus {
            background: #081d38;
            border-color: #0dcaf0;
            color: #ffffff;
            box-shadow: 0 0 0 0.15rem rgba(13, 202, 240, 0.12);
        }
        .form-select option {
            background: #0b2445;
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
            color: #718caf;
        }
        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
        }
        @media (max-width: 768px) {
            .page-wrapper {
                padding: 18px;
            }
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }
            .page-title h1 {
                font-size: 21px;
            }
            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>

</head>

<body>

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
        <a href="index.php" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Dashboard
        </a>
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

<!-- MODAL EDIT USER -->
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
                            class="form-control"
                            id="edit_nama"
                            readonly
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            NIPP
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="edit_nipp"
                            readonly
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
});

</script>

</body>
</html>
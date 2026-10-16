<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

// ======================================================
// AKUN SUPER ADMIN
// Login langsung dari coding, tidak disimpan di database
// ======================================================
$superAdminNipp = '000000';
$superAdminPassword = 'SuperAdmin123';

if (isset($_GET['register']) && $_GET['register'] === 'success') {
    $success = 'Registrasi berhasil. Silakan menunggu aktivasi dari Super Admin.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nipp = trim($_POST['nipp'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nipp === '' || $password === '') {

        $error = 'NIPP dan password wajib diisi.';

    } else {

        // LOGIN SUPER ADMIN
        if ($nipp === $superAdminNipp && $password === $superAdminPassword) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = 0;
            $_SESSION['user_nama'] = 'Super Admin';
            $_SESSION['user_nipp'] = $superAdminNipp;
            $_SESSION['user_role'] = 'super_admin';
            $_SESSION['user_depo'] = 'SEMUA';

            header('Location: index.php');
            exit;
        }

        // LOGIN USER BIASA
        try {

            $stmt = $pdo->prepare("
                SELECT id, nama, nipp, depo, password, role, status
                FROM users
                WHERE nipp = ?
                LIMIT 1
            ");

            $stmt->execute([$nipp]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cek NIPP dan password
            if (!$user || !password_verify($password, $user['password'])) {

                $error = 'NIPP atau password salah.';

            // Cek status akun
            } elseif ($user['status'] !== 'aktif') {

                $error = 'Akun Anda belum diaktifkan oleh Super Admin.';

            } else {

                // LOGIN BERHASIL
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_nipp'] = $user['nipp'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_depo'] = $user['depo'];

                header('Location: index.php');
                exit;
            }

        } catch (PDOException $e) {

            $error = 'Terjadi kesalahan saat login.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RailMap</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <div class="brand">
            <div class="brand-icon">
                ≡
            </div>

            <div>
                <h1>RailMap</h1>
                <span>Railway Monitoring System</span>
            </div>
        </div>

        <div class="auth-title">
            <h2>Selamat Datang</h2>
            <p>Masuk untuk mengakses sistem monitoring</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>NIPP</label>
                <input
                    type="text"
                    name="nipp"
                    placeholder="Masukkan NIPP"
                    value="<?= htmlspecialchars($_POST['nipp'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn-auth">
                Masuk
            </button>

        </form>

        <div class="auth-footer">
            Belum memiliki akun?
            <a href="register.php">Daftar sekarang</a>
        </div>

    </div>

</div>

</body>
</html>
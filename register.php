<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nipp = trim($_POST['nipp'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $nipp === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua data wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sesuai.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE nipp = ?");
            $stmt->execute([$nipp]);

            if ($stmt->fetch()) {
                $error = 'NIPP sudah terdaftar.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (nama, nipp, password)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $nama,
                    $nipp,
                    $hashedPassword
                ]);

                header('Location: login.php?register=success');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan saat membuat akun.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RailMap</title>
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
            <h2>Buat Akun</h2>
            <p>Daftarkan akun untuk mengakses RailMap</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input
                    type="text"
                    name="nama"
                    placeholder="Masukkan nama lengkap"
                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                    required
                >
            </div>

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
                    placeholder="Minimal 6 karakter"
                    required
                >
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input
                    type="password"
                    name="konfirmasi"
                    placeholder="Ulangi password"
                    required
                >
            </div>

            <button type="submit" class="btn-auth">
                Daftar
            </button>

        </form>

        <div class="auth-footer">
            Sudah memiliki akun?
            <a href="login.php">Masuk sekarang</a>
        </div>

    </div>

</div>

</body>
</html>
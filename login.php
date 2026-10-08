<?php
session_start();
require_once 'db.php';

$error = '';

if (isset($_GET['register']) && $_GET['register'] === 'success') {
    $success = 'Registrasi berhasil. Silakan masuk.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {

        try {
            $stmt = $pdo->prepare("
                SELECT id, nama, email, password
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];

                header('Location: index.php');
                exit;

            } else {
                $error = 'Email atau password salah.';
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
                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
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
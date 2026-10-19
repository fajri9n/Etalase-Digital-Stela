<?php
session_start();
require_once '../config/kuis_db.php';

// Jika sudah login, redirect ke beranda
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($password_raw)) {
        $error = "Semua bidang wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($password_raw) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $error = "Email sudah terdaftar, silakan gunakan email lain atau login.";
        } else {
            $password = password_hash($password_raw, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'siswa')");
                $stmt->execute([':nama' => $nama, ':email' => $email, ':password' => $password]);
                header('Location: login.php?registered=1');
                exit();
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan pada sistem: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Siswa - Etalase Digital Stella</title>
    <!-- Hubungkan CSS Base & CSS Auth -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Registrasi Siswa</h2>

        <?php if (!empty($error)): ?>
            <div class="alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-control">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Nama Lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-control">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@stella.sch.id" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-control">
                <label for="password">Password (min. 6 karakter)</label>
                <input type="password" id="password" name="password" placeholder="Buat password aman" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 5px; padding: 10px;">Daftar Akun</button>
        </form>

        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>
</body>
</html>

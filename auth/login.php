<?php
session_start();
require_once '../config/kuis_db.php';

// Jika sudah login, redirect langsung ke beranda
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Registrasi berhasil! Silakan login dengan akun Anda.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            header('Location: ../index.php');
            exit();
        } else {
            $error = "Email atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Etalase Digital Stella</title>
    <!-- Hubungkan CSS Base & CSS Auth dengan path relatif yang benar -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Login Etalase</h2>

        <?php if (!empty($success)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-control">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@stella.sch.id" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-control">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 5px; padding: 10px;">Login</button>
        </form>

        <div class="auth-footer">
            <p>Siswa baru? <a href="register.php">Daftar Akun di sini</a></p>
        </div>
    </div>
</div>
</body>
</html>

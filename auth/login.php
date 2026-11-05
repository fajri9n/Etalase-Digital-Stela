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

if (isset($_GET['reset'])) {
    $success = "Password berhasil direset! Silakan login dengan password baru Anda.";
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
            $error = "Email atau kata sandi yang Anda masukkan salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stella Etalase Digital</title>
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS Auth -->
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-page">
    <!-- Ambient Floating Glows -->
    <div class="ambient-glow ambient-glow-1"></div>
    <div class="ambient-glow ambient-glow-2"></div>
    <div class="ambient-glow ambient-glow-3"></div>

    <!-- Top Navigation Bar -->
    <header class="auth-top-bar">
        <a href="../index.php" class="back-home-btn" title="Kembali ke Beranda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="auth-top-brand-badge">
            <span class="badge-dot"></span>
            <span>Stella Etalase Digital</span>
        </div>
    </header>

    <!-- Main Centered Box Card (Kotak: Kiri Video, Kanan Form) -->
    <main class="auth-card-wrapper">
        <div class="auth-box-card">

            <!-- SEBELAH KIRI: VIDEO SHOWCASE -->
            <div class="auth-video-pane">
                <video class="auth-video-player" id="authVideo" autoplay muted loop playsinline>
                    <source src="../assets/img/tunggu.mp4" type="video/mp4">
                    Browser kamu tidak mendukung pemutaran video.
                </video>

                <div class="video-overlay">
                    <div class="video-badge">
                        <span class="live-dot"></span>
                        <span>STELLA ETALASE DIGITAL</span>
                    </div>


                    <div class="video-caption">
                        <span class="video-tagline">BELAJAR • BERKARYA • BERKEMBANG</span>
                        <h2 class="video-title">Panggung Talenta Stella Etalase Digital</h2>
                        <p class="video-desc">Eksplorasi dan apresiasi karya inovatif, proyek teknologi, dan portofolio unggulan siswa.</p>
                        
                        <div class="video-highlights">
                            <span class="highlight-chip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                Proyek Siswa
                            </span>
                            <span class="highlight-chip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                                Portofolio Digital
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEBELAH KANAN: FORM -->
            <div class="auth-form-pane">
                <div class="auth-form-inner">
                    <!-- Brand / School Header -->
                    <div class="auth-brand-row">
                        <img src="../assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
                        <div class="brand-meta">
                            <span class="brand-school">STELLA ETALASE DIGITAL</span>
                            <span class="brand-portal">Portal Karya & Inovasi Siswa</span>
                        </div>
                    </div>

                    <!-- Header Title -->
                    <div class="auth-header">
                        <h1 class="auth-title">Masuk ke Akun</h1>
                        <p class="auth-subtitle">Silakan login untuk mengelola portofolio atau mengakses fitur siswa.</p>
                    </div>

                    <!-- Notifications / Alerts -->
                    <?php if (!empty($success)): ?>
                        <div class="auth-alert auth-alert-success auth-alert-dismissible" role="alert">
                            <svg class="auth-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="auth-alert auth-alert-danger" role="alert">
                            <svg class="auth-alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="login.php" autocomplete="on">
                        <div class="form-group">
                            <label for="email" class="form-label">Alamat Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </span>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="input-field" 
                                    placeholder="nama@stella.sch.id" 
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                    required 
                                    autofocus 
                                    autocomplete="email"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-label-row">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <a href="lupa_sandi.php" class="form-link-muted">Lupa sandi?</a>
                            </div>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </span>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="input-field" 
                                    placeholder="Masukkan kata sandi akun" 
                                    required 
                                    autocomplete="current-password"
                                >
                                <button type="button" class="toggle-password-btn" data-target="password" aria-label="Tampilkan kata sandi">
                                    <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth-submit">
                            <span>Masuk Sekarang</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </form>

                    <!-- Footer Link -->
                    <div class="auth-card-footer">
                        <p>Belum memiliki akun? <a href="register.php">Daftar Akun Siswa</a></p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Bottom Copyright -->
    <footer class="auth-bottom-footer">
        &copy; <?= date('Y') ?> Stella Etalase Digital. Seluruh Hak Cipta Dilindungi.
    </footer>
</div>

<!-- Interactive JS Controller -->
<script src="../assets/js/auth.js"></script>
</body>
</html>

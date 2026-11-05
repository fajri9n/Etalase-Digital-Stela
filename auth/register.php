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
    <title>Registrasi Siswa - Stella Etalase Digital</title>
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

                    <button type="button" class="video-sound-toggle" id="videoSoundToggle" title="Nyalakan / Bisukan Suara" aria-label="Toggle suara video">
                        <svg class="sound-icon-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <line x1="23" y1="9" x2="17" y2="15"></line>
                            <line x1="17" y1="9" x2="23" y2="15"></line>
                        </svg>
                        <svg class="sound-icon-unmuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </button>

                    <div class="video-caption">
                        <span class="video-tagline">MULAI PERJALANAN KREATIFMU</span>
                        <h2 class="video-title">Tampilkan Karyamu di Panggung Nyata</h2>
                        <p class="video-desc">Daftarkan akun siswa sekarang dan bagikan proyek, karya inovatif, dan portofolio terbaikmu ke dunia.</p>
                        
                        <div class="video-highlights">
                            <span class="highlight-chip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                Pendaftaran Cepat
                            </span>
                            <span class="highlight-chip">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7.5" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                Akses Akun Siswa
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
                            <span class="brand-portal">Registrasi Akun Siswa</span>
                        </div>
                    </div>

                    <!-- Header Title -->
                    <div class="auth-header">
                        <h1 class="auth-title">Daftar Akun Baru</h1>
                        <p class="auth-subtitle">Isi formulir berikut untuk membuat akun siswa di Stella Etalase Digital.</p>
                    </div>

                    <!-- Notifications / Alerts -->
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

                    <!-- Registration Form -->
                    <form method="POST" action="register.php" autocomplete="on">
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="nama" 
                                    name="nama" 
                                    class="input-field" 
                                    placeholder="Contoh: Budi Santoso" 
                                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" 
                                    required 
                                    autofocus 
                                    autocomplete="name"
                                >
                            </div>
                        </div>

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
                                    placeholder="email@stella.sch.id" 
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                    required 
                                    autocomplete="email"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-label-row">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <span style="font-size: 11.5px; color: #94a3b8;">Min. 6 karakter</span>
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
                                    placeholder="Buat kata sandi yang aman" 
                                    minlength="6"
                                    required 
                                    autocomplete="new-password"
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
                            <span>Daftar Akun Sekarang</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7.5" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                        </button>
                    </form>

                    <!-- Footer Link -->
                    <div class="auth-card-footer">
                        <p>Sudah memiliki akun? <a href="login.php">Masuk di sini</a></p>
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

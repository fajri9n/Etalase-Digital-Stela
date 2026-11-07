<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$user = get_logged_user($pdo);
if (!$user) {
    header('Location: auth/logout.php');
    exit();
}

$success = '';
$error = '';

// Handle Update Profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nama) || empty($email)) {
            $error = "Nama dan email wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } else {
            // Cek apakah email sudah dipakai oleh pengguna lain
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $checkEmail->execute([':email' => $email, ':id' => $user['id']]);
            if ($checkEmail->fetch()) {
                $error = "Email sudah digunakan oleh akun lain! Silakan gunakan email lain.";
            } else {
                $foto_nama = $user['foto'];

                // Cek apakah ada file foto profil yang diunggah
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    $file_info = pathinfo($_FILES['foto']['name']);
                    $ext = strtolower($file_info['extension'] ?? '');

                    if (!in_array($ext, $allowed_exts)) {
                        $error = "Format foto tidak didukung! Gunakan JPG, PNG, WEBP, atau GIF.";
                    } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                        $error = "Ukuran foto terlalu besar! Maksimal 5MB.";
                    } else {
                        if (!is_dir('uploads/avatars')) {
                            mkdir('uploads/avatars', 0777, true);
                        }

                        $new_avatar_name = 'avatar_' . uniqid() . '.' . $ext;
                        $target_path = 'uploads/avatars/' . $new_avatar_name;

                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                            // Hapus foto profil lama jika ada
                            if (!empty($user['foto']) && file_exists('uploads/avatars/' . $user['foto'])) {
                                @unlink('uploads/avatars/' . $user['foto']);
                            }
                            $foto_nama = $new_avatar_name;
                        } else {
                            $error = "Gagal mengunggah foto profil ke server.";
                        }
                    }
                }

                if (empty($error)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET nama = :nama, email = :email, foto = :foto WHERE id = :id");
                        $stmt->execute([
                            ':nama'  => $nama,
                            ':email' => $email,
                            ':foto'  => $foto_nama,
                            ':id'    => $user['id']
                        ]);

                        $success = "Profil Anda berhasil diperbarui!";
                        // Refresh data user
                        $user = get_logged_user($pdo);
                    } catch (PDOException $e) {
                        $error = "Terjadi kesalahan saat menyimpan profil: " . $e->getMessage();
                    }
                }
            }
        }
    } elseif ($_POST['action'] === 'hapus_foto') {
        if (!empty($user['foto']) && file_exists('uploads/avatars/' . $user['foto'])) {
            @unlink('uploads/avatars/' . $user['foto']);
        }
        try {
            $stmt = $pdo->prepare("UPDATE users SET foto = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $success = "Foto profil berhasil dihapus dan dikembalikan ke inisial default.";
            $user = get_logged_user($pdo);
        } catch (PDOException $e) {
            $error = "Gagal menghapus foto profil: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'update_password') {
        $password_lama       = $_POST['password_lama'] ?? '';
        $password_baru       = $_POST['password_baru'] ?? '';
        $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $error = "Semua bidang kata sandi wajib diisi!";
        } elseif (strlen($password_baru) < 6) {
            $error = "Kata sandi baru minimal 6 karakter!";
        } elseif ($password_baru !== $konfirmasi_password) {
            $error = "Konfirmasi kata sandi baru tidak cocok!";
        } else {
            // Cek password lama
            $checkPass = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $checkPass->execute([':id' => $user['id']]);
            $userData = $checkPass->fetch();

            if (!$userData || !password_verify($password_lama, $userData['password'])) {
                $error = "Kata sandi saat ini (lama) yang Anda masukkan salah!";
            } else {
                $new_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                try {
                    $updatePass = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :id");
                    $updatePass->execute([':pass' => $new_hash, ':id' => $user['id']]);
                    $success = "Kata sandi Anda berhasil diperbarui dengan aman!";
                } catch (PDOException $e) {
                    $error = "Gagal memperbarui kata sandi: " . $e->getMessage();
                }
            }
        }
    }
}

// Ambil semua proyek yang dibuat oleh user ini beserta total like
try {
    $stmtProjects = $pdo->prepare("
        SELECT 
            projects.*, 
            COALESCE(likes_count.total_likes, 0) AS total_likes 
        FROM projects 
        LEFT JOIN (
            SELECT project_id, COUNT(*) AS total_likes 
            FROM project_likes 
            GROUP BY project_id
        ) AS likes_count ON projects.id = likes_count.project_id
        WHERE projects.user_id = :uid 
        ORDER BY projects.created_at DESC
    ");
    $stmtProjects->execute([':uid' => $user['id']]);
    $myProjects = $stmtProjects->fetchAll();
} catch (PDOException $e) {
    $stmtProjects = $pdo->prepare("SELECT *, 0 AS total_likes FROM projects WHERE user_id = :uid ORDER BY created_at DESC");
    $stmtProjects->execute([':uid' => $user['id']]);
    $myProjects = $stmtProjects->fetchAll();
}
$totalMyProjects = count($myProjects);

// Data Inisial & Role
$user_nama = htmlspecialchars($user['nama'] ?? 'User');
$user_email = htmlspecialchars($user['email'] ?? '');
$user_role = htmlspecialchars($user['role'] ?? 'siswa');
$user_foto = $user['foto'] ?? null;

$initials = '';
$words = preg_split('/\s+/', trim($user_nama));
foreach (array_slice($words, 0, 2) as $w) {
    if (!empty($w)) $initials .= strtoupper(mb_substr($w, 0, 1));
}
if (empty($initials)) $initials = 'U';

$role_title = ucwords(str_replace('_', ' ', $user_role));
$role_icon = '🎓';
if ($user_role === 'super_admin') {
    $role_icon = '👑';
} elseif ($user_role === 'admin') {
    $role_icon = '🛡️';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Stella Etalase Digital</title>
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>

<!-- Background Foto Gedung Sekolah SMK Stella dengan Efek Overlay -->
<div class="bg-overlay"></div>

<!-- Modern Glassmorphism Navbar -->
<nav class="navbar" id="mainNavbar">
    <!-- Brand Info -->
    <a href="index.php" class="navbar-brand-link" title="Beranda Stella Etalase Digital">
        <div class="brand-logo-frame">
            <img src="assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
        </div>
        <div class="brand-text-col">
            <h1 class="brand-main-title">Stella <span class="brand-gradient-text">Etalase Digital</span></h1>
            <div class="brand-meta-row">
                <span class="brand-badge-pill">
                    <span class="brand-live-dot"></span> SMK Telkom Lampung
                </span>
                <span class="brand-page-tag">&bull; Profil Pengguna</span>
            </div>
        </div>
    </a>

    <!-- Desktop Menu & User Actions -->
    <div class="navbar-menu-group">
        <!-- User Capsule Link ke Profil -->
        <a href="profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" title="Kelola Profil Saya" style="text-decoration: none;">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('uploads/avatars/' . $user_foto)): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="user-meta-text">
                <span class="user-greeting-label">Akun Saya,</span>
                <span class="user-name-label"><?= $user_nama ?></span>
            </div>
            <span class="role-badge-pill role-badge-<?= $user_role ?>">
                <?= $role_icon ?> <?= $role_title ?>
            </span>
        </a>

        <a href="index.php" class="nav-btn btn-nav-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Katalog Karya</span>
        </a>

        <a href="project_tambah.php" class="nav-btn btn-nav-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ Tambah Karya</span>
        </a>

        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="nav-btn btn-nav-warning" title="Kelola Manajemen Pengguna">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Kelola Users</span>
            </a>
        <?php endif; ?>

        <a href="auth/logout.php" class="nav-btn btn-nav-danger" title="Keluar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Logout</span>
        </a>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="navbar-toggle-btn" id="navbarToggle" aria-label="Menu Navigasi">
        <span class="navbar-toggle-bar"></span>
        <span class="navbar-toggle-bar"></span>
        <span class="navbar-toggle-bar"></span>
    </button>

    <!-- Mobile Drawer Panel -->
    <div class="navbar-mobile-drawer" id="navbarMobileDrawer">
        <div class="navbar-user-capsule user-capsule-<?= $user_role ?>">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('uploads/avatars/' . $user_foto)): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="user-meta-text">
                <span class="user-greeting-label">Akun Saya,</span>
                <span class="user-name-label"><?= $user_nama ?></span>
            </div>
            <span class="role-badge-pill role-badge-<?= $user_role ?>">
                <?= $role_icon ?> <?= $role_title ?>
            </span>
        </div>

        <a href="profile.php" class="nav-btn btn-nav-secondary">
            <span>👤 Halaman Profil Saya</span>
        </a>
        <a href="index.php" class="nav-btn btn-nav-secondary">
            <span>🖼️ Katalog Karya</span>
        </a>
        <a href="project_tambah.php" class="nav-btn btn-nav-primary">
            <span>+ Tambah Karya Digital</span>
        </a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="nav-btn btn-nav-warning">
                <span>🛡️ Kelola Users</span>
            </a>
        <?php endif; ?>
        <a href="auth/logout.php" class="nav-btn btn-nav-danger">
            <span>🚪 Logout</span>
        </a>
    </div>
</nav>

<!-- Main Profile Page Body -->
<main class="profile-container">

    <!-- Feedback Alerts -->
    <?php if (!empty($success)): ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 14px; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; font-size: 13.5px; font-weight: 600; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.1);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 14px; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; font-size: 13.5px; font-weight: 600; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.1);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- PROFILE HERO CARD -->
    <section class="profile-hero-card">
        <div class="profile-hero-main">
            <!-- Avatar Frame -->
            <div class="profile-avatar-frame">
                <?php if (!empty($user_foto) && file_exists('uploads/avatars/' . $user_foto)): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>" class="profile-avatar-img" id="heroAvatarImg">
                <?php else: ?>
                    <div class="profile-avatar-fallback" id="heroAvatarFallback"><?= $initials ?></div>
                <?php endif; ?>
            </div>

            <!-- Meta User -->
            <div class="profile-hero-meta">
                <h1><?= $user_nama ?></h1>
                <p class="profile-hero-email">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span><?= $user_email ?></span>
                </p>
                <div class="profile-badge-row">
                    <span class="profile-role-badge profile-role-<?= $user_role ?>">
                        <?= $role_icon ?> <?= $role_title ?>
                    </span>
                    <span class="profile-status-pill">
                        <span class="profile-status-dot"></span>
                        <span>Akun Aktif</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="profile-hero-stats">
            <div class="stat-item">
                <span class="stat-number"><?= $totalMyProjects ?></span>
                <span class="stat-label">Karya Diunggah</span>
            </div>
        </div>
    </section>

    <!-- SETTINGS PANELS (2 COLUMNS) -->
    <div class="profile-grid-layout">

        <!-- KOLOM 1: EDIT PROFIL & FOTO -->
        <section class="profile-card">
            <div class="profile-card-header">
                <div class="card-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <h2>Informasi Profil</h2>
                    <p>Ubah nama, email, dan pasang foto profil terbaikmu.</p>
                </div>
            </div>

            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">

                <!-- UPLOAD FOTO PROFIL SECTION -->
                <div class="avatar-upload-section">
                    <div class="avatar-preview-box" id="avatarPreviewContainer">
                        <?php if (!empty($user_foto) && file_exists('uploads/avatars/' . $user_foto)): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="Foto Profil" id="avatarPreviewImg">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e2e8f0; font-weight: 800; font-size: 22px; color: #475569;" id="avatarPreviewFallback">
                                <?= $initials ?>
                            </div>
                            <img src="" alt="Preview Foto" id="avatarPreviewImg" style="display: none;">
                        <?php endif; ?>
                    </div>

                    <div class="avatar-upload-content">
                        <label for="fotoInput">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <span>Pilih Foto Baru</span>
                        </label>
                        <input type="file" id="fotoInput" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <span class="avatar-hint">Format JPG, PNG, WEBP. Maksimal 5MB.</span>

                        <?php if (!empty($user_foto)): ?>
                            <button type="submit" name="action" value="hapus_foto" class="btn-remove-avatar" onclick="return confirm('Hapus foto profil dan kembali ke inisial default?')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Hapus Foto Profil
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Input Nama -->
                <div class="form-field-group">
                    <label for="nama">Nama Lengkap</label>
                    <div class="form-field-wrapper">
                        <span class="field-icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" id="nama" name="nama" class="form-field-input" value="<?= $user_nama ?>" required placeholder="Nama lengkap Anda">
                    </div>
                </div>

                <!-- Input Email -->
                <div class="form-field-group">
                    <label for="email">Alamat Email</label>
                    <div class="form-field-wrapper">
                        <span class="field-icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" class="form-field-input" value="<?= $user_email ?>" required placeholder="email@stella.sch.id">
                    </div>
                </div>

                <!-- Tombol Simpan Profil -->
                <button type="submit" class="btn-primary-gradient">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </form>
        </section>

        <!-- KOLOM 2: GANTI KATA SANDI -->
        <section class="profile-card">
            <div class="profile-card-header">
                <div class="card-header-icon" style="background: #eff6ff; color: #2563eb; border-color: #bfdbfe;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div>
                    <h2>Keamanan Akun</h2>
                    <p>Perbarui kata sandi akun Anda secara berkala.</p>
                </div>
            </div>

            <form method="POST" action="profile.php" autocomplete="off">
                <input type="hidden" name="action" value="update_password">

                <!-- Password Lama -->
                <div class="form-field-group">
                    <label for="password_lama">Kata Sandi Saat Ini</label>
                    <div class="form-field-wrapper">
                        <span class="field-icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" id="password_lama" name="password_lama" class="form-field-input" placeholder="Masukkan kata sandi lama" required>
                    </div>
                </div>

                <!-- Password Baru -->
                <div class="form-field-group">
                    <label for="password_baru">Kata Sandi Baru (Min. 6 Karakter)</label>
                    <div class="form-field-wrapper">
                        <span class="field-icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 2l-2 2m-2-2l2 2"></path>
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" id="password_baru" name="password_baru" minlength="6" class="form-field-input" placeholder="Masukkan kata sandi baru" required>
                    </div>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="form-field-group">
                    <label for="konfirmasi_password">Ulangi Kata Sandi Baru</label>
                    <div class="form-field-wrapper">
                        <span class="field-icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </span>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" minlength="6" class="form-field-input" placeholder="Ketik ulang kata sandi baru" required>
                    </div>
                </div>

                <!-- Tombol Update Password -->
                <button type="submit" class="btn-primary-gradient" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 6px 18px rgba(37, 99, 235, 0.3);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Perbarui Kata Sandi</span>
                </button>
            </form>
        </section>

    </div>

    <!-- KOLEKSI KARYA SAYA -->
    <section class="user-projects-section">
        <div class="user-projects-header">
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 4px;">Koleksi Karya Saya</h2>
                <p style="font-size: 13px; color: #64748b; margin: 0;">Kelola seluruh karya digital yang telah Anda publikasikan di Stella Etalase Digital.</p>
            </div>
            <a href="project_tambah.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 13px;">
                <span>+ Unggah Karya Baru</span>
            </a>
        </div>

        <?php if (empty($myProjects)): ?>
            <div class="empty-karya-box">
                <div style="font-size: 44px; margin-bottom: 10px;">🚀</div>
                <h3 style="font-size: 17px; font-weight: 700; color: #334155; margin-bottom: 6px;">Belum Ada Karya yang Diunggah</h3>
                <p style="font-size: 13px; color: #64748b; max-width: 440px; margin: 0 auto 18px;">Mulai bagikan kreasi terbaikmu, aplikasi web, atau proyek teknologi agar dapat diapresiasi oleh seluruh sekolah.</p>
                <a href="project_tambah.php" class="btn btn-primary" style="padding: 8px 18px; border-radius: 8px; font-size: 13px;">+ Unggah Karya Pertamamu</a>
            </div>
        <?php else: ?>
            <div class="user-projects-grid">
                <?php foreach ($myProjects as $proj): ?>
                    <div class="user-project-card">
                        <div class="user-project-thumb">
                            <img src="uploads/<?= htmlspecialchars($proj['gambar']) ?>" alt="<?= htmlspecialchars($proj['nama_project']) ?>" onerror="this.onerror=null; this.src='assets/default_project.svg';">
                        </div>
                        <div class="user-project-body">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; font-size: 11.5px;">
                                <?php 
                                $p_kat = $proj['kategori'] ?? 'Web Design';
                                $p_info = get_category_info($p_kat);
                                ?>
                                <span style="font-weight: 700; color: #2563eb; background: #eff6ff; padding: 2px 8px; border-radius: 6px;">
                                    <?= $p_info['icon'] ?> <?= htmlspecialchars($p_kat) ?>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: #dc2626;">
                                    ❤️ <?= (int) ($proj['total_likes'] ?? 0) ?> Suka
                                </span>
                            </div>
                            <h3 class="user-project-title"><?= htmlspecialchars($proj['nama_project']) ?></h3>
                            <span class="user-project-date">📅 <?= date('d M Y', strtotime($proj['created_at'])) ?></span>
                            
                            <div class="user-project-actions">
                                <a href="<?= htmlspecialchars($proj['link_project']) ?>" target="_blank" rel="noopener noreferrer" class="btn-action-sm btn-view-sm">Lihat Web</a>
                                <a href="project_edit.php?id=<?= $proj['id'] ?>" class="btn-action-sm btn-edit-sm">Edit</a>
                                <a href="project_hapus.php?id=<?= $proj['id'] ?>" onclick="return confirm('Hapus karya ini dari portofolio?')" class="btn-action-sm btn-delete-sm">Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<!-- Interactive Script for Live Avatar Preview & Navbar -->
<script src="assets/js/navbar.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const fotoInput = document.getElementById("fotoInput");
    const previewImg = document.getElementById("avatarPreviewImg");
    const previewFallback = document.getElementById("avatarPreviewFallback");

    if (fotoInput) {
        fotoInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = "block";
                    }
                    if (previewFallback) {
                        previewFallback.style.display = "none";
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
</body>
</html>

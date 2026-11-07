<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$user = get_logged_user($pdo);
if (!$user) {
    header('Location: auth/logout.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $kategori     = trim($_POST['kategori'] ?? 'Web Design');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');

    // Validasi daftar kategori
    $valid_categories = array_keys(get_project_categories());
    if (!in_array($kategori, $valid_categories)) {
        $kategori = 'Web Design';
    }

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua bidang teks wajib diisi!";
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = "Gambar sampul karya wajib diunggah!";
    } else {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $file_info = pathinfo($_FILES['gambar']['name']);
        $ext = strtolower($file_info['extension'] ?? '');

        if (!in_array($ext, $allowed_exts)) {
            $error = "Format gambar tidak didukung! Gunakan format JPG, PNG, WEBP, atau GIF.";
        } elseif ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
            $error = "Ukuran gambar terlalu besar! Maksimal 5MB.";
        } else {
            // Pastikan folder uploads tersedia
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $new_name = uniqid('project_', true) . '.' . $ext;
            $destination = 'uploads/' . $new_name;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, kategori, deskripsi, gambar, link_project) 
                                           VALUES (:user_id, :nama, :kat, :desk, :img, :link)");
                    $stmt->execute([
                        ':user_id' => $user['id'],
                        ':nama'    => $nama_project,
                        ':kat'     => $kategori,
                        ':desk'    => $deskripsi,
                        ':img'     => $new_name,
                        ':link'    => $link_project
                    ]);
                    header('Location: index.php?success=1');
                    exit();
                } catch (PDOException $e) {
                    $error = "Gagal menyimpan karya ke database: " . $e->getMessage();
                }
            } else {
                $error = "Gagal mengunggah file gambar ke server. Pastikan folder uploads dapat ditulis.";
            }
        }
    }
}

// Data User untuk Navbar
$user_nama = htmlspecialchars($user['nama'] ?? 'User');
$user_role = htmlspecialchars($user['role'] ?? 'siswa');
$user_foto = $user['foto'] ?? null;

$initials = '';
$words = preg_split('/\s+/', trim($user_nama));
foreach (array_slice($words, 0, 2) as $w) {
    if (!empty($w)) $initials .= strtoupper(mb_substr($w, 0, 1));
}
if (empty($initials)) $initials = 'U';

$role_badge_class = 'role-badge-' . $user_role;
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
    <title>Tambah Karya - Stella Etalase Digital</title>
    <!-- Modern Typography Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>

<!-- Background Foto Gedung Sekolah SMK Stella dengan Efek Overlay -->
<div class="bg-overlay"></div>

<!-- Modern Glassmorphism Navbar -->
<nav class="navbar" id="mainNavbar">
    <!-- Brand Info -->
    <a href="index.php" class="navbar-brand-link" title="Kembali ke Beranda">
        <div class="brand-logo-frame">
            <img src="assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
        </div>
        <div class="brand-text-col">
            <h1 class="brand-main-title">Stella <span class="brand-gradient-text">Etalase Digital</span></h1>
            <div class="brand-meta-row">
                <span class="brand-badge-pill">
                    <span class="brand-live-dot"></span> SMK STELLA MARIS
                </span>
                <span class="brand-page-tag">&bull; Tambah Karya</span>
            </div>
        </div>
    </a>

    <!-- Desktop Menu & User Actions -->
    <div class="navbar-menu-group">
        <!-- User Capsule Link ke Profil -->
        <a href="profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" title="Buka Profil Saya" style="text-decoration: none;">
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
            <span class="role-badge-pill <?= $role_badge_class ?>">
                <?= $role_icon ?> <?= $role_title ?>
            </span>
        </a>

        <a href="index.php" class="nav-btn btn-nav-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali ke Galeri</span>
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
        <a href="profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" style="text-decoration: none;">
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
            <span class="role-badge-pill <?= $role_badge_class ?>">
                <?= $role_icon ?> <?= $role_title ?>
            </span>
        </a>

        <a href="profile.php" class="nav-btn btn-nav-secondary">
            <span>👤 Profil Saya</span>
        </a>
        <a href="index.php" class="nav-btn btn-nav-secondary">
            <span>🖼️ Kembali ke Galeri</span>
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

<!-- Main Page Form Container -->
<main class="form-page-container">
    <div class="form-card">

        <!-- Form Card Header -->
        <div class="form-card-header">
            <div class="form-header-badge">
                <span class="badge-dot"></span>
                <span>PUBLIKASI KARYA SISWA</span>
            </div>
            <h1 class="form-card-title">Unggah Karya Inovasi Baru</h1>
            <p class="form-card-subtitle">
                Bagikan proyek web, aplikasi, desain, atau inovasi teknologi terbaikmu di Stella Etalase Digital.
            </p>
        </div>

        <!-- Alert Error Message -->
        <?php if (!empty($error)): ?>
            <div class="form-alert-danger" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Karya -->
        <form action="project_tambah.php" method="POST" enctype="multipart/form-data" id="projectForm">
            
            <!-- Judul Karya -->
            <div class="form-group">
                <div class="form-group-label">
                    <label for="nama_project">Judul / Nama Karya</label>
                    <span class="label-hint">Wajib diisi</span>
                </div>
                <div class="form-input-wrapper">
                    <span class="input-leading-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        id="nama_project" 
                        name="nama_project" 
                        class="form-input-text" 
                        placeholder="Contoh: Sistem Manajemen Absensi Berbasis QR Code" 
                        value="<?= htmlspecialchars($_POST['nama_project'] ?? '') ?>" 
                        required 
                        autofocus
                    >
                </div>
            </div>

            <!-- Kategori Karya -->
            <div class="form-group">
                <div class="form-group-label">
                    <label for="kategori">Kategori Karya</label>
                    <span class="label-hint">Pilih bidang karya</span>
                </div>
                <div class="form-input-wrapper">
                    <span class="input-leading-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <select id="kategori" name="kategori" class="form-select" required>
                        <?php 
                        $daftar_kategori = get_project_categories();
                        $selected_kat = $_POST['kategori'] ?? 'Web Design';
                        foreach ($daftar_kategori as $kat_name => $kat_meta): 
                        ?>
                            <option value="<?= htmlspecialchars($kat_name) ?>" <?= ($selected_kat === $kat_name) ? 'selected' : '' ?>>
                                <?= $kat_meta['icon'] ?> <?= htmlspecialchars($kat_name) ?> — <?= htmlspecialchars($kat_meta['desc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tautan / Link Karya -->
            <div class="form-group">
                <div class="form-group-label">
                    <label for="link_project">Tautan Proyek / Demo / GitHub</label>
                    <span class="label-hint">URL Aktif</span>
                </div>
                <div class="form-input-wrapper">
                    <span class="input-leading-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </span>
                    <input 
                        type="url" 
                        id="link_project" 
                        name="link_project" 
                        class="form-input-url" 
                        placeholder="https://github.com/username/project atau https://demo-karya.com" 
                        value="<?= htmlspecialchars($_POST['link_project'] ?? '') ?>" 
                        required
                    >
                </div>
            </div>

            <!-- Deskripsi Karya -->
            <div class="form-group">
                <div class="form-group-label">
                    <label for="deskripsi">Deskripsi Karya</label>
                    <span class="label-hint">Jelaskan fitur & teknologi yang digunakan</span>
                </div>
                <div class="form-input-wrapper">
                    <span class="input-leading-icon" style="top: 16px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </span>
                    <textarea 
                        id="deskripsi" 
                        name="deskripsi" 
                        class="form-textarea" 
                        placeholder="Tuliskan latar belakang karya, fitur utama, serta tools / bahasa pemrograman yang Anda gunakan..." 
                        required
                    ><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Unggah Gambar Cover / Mockup -->
            <div class="form-group">
                <div class="form-group-label">
                    <label>Gambar Cover / Mockup Karya</label>
                    <span class="label-hint">Format JPG, PNG, WEBP (Maks. 5MB)</span>
                </div>

                <div class="upload-dropzone" id="dropzoneArea">
                    <input 
                        type="file" 
                        id="gambarInput" 
                        name="gambar" 
                        class="upload-dropzone-input" 
                        accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" 
                        required
                    >
                    <div class="dropzone-icon-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <p class="dropzone-title">Tarik & letakkan gambar karya di sini, atau <span>Pilih dari Komputer</span></p>
                    <p class="dropzone-desc">Disarankan menggunakan rasio 16:9 atau mockup beresolusi tajam.</p>
                </div>

                <!-- Preview Gambar yang Diunggah -->
                <div class="upload-preview-container" id="previewContainer">
                    <img src="" alt="Preview Karya" class="upload-preview-img" id="previewImg">
                    <div class="preview-overlay-info">
                        <span class="preview-filename" id="previewFilename">nama-file.png</span>
                        <button type="button" class="btn-change-image" id="btnChangeImage">Ganti Gambar</button>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="form-actions-row">
                <a href="index.php" class="btn-form-cancel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Batal & Kembali</span>
                </a>

                <button type="submit" class="btn-form-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Publikasikan Karya Sekarang</span>
                </button>
            </div>

        </form>
    </div>
</main>

<!-- Interactive Scripts -->
<script src="assets/js/navbar.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("gambarInput");
    const dropzone = document.getElementById("dropzoneArea");
    const previewContainer = document.getElementById("previewContainer");
    const previewImg = document.getElementById("previewImg");
    const previewFilename = document.getElementById("previewFilename");
    const btnChange = document.getElementById("btnChangeImage");

    if (fileInput && previewContainer) {
        fileInput.addEventListener("change", function () {
            handleFileSelect(this.files[0]);
        });

        // Drag & Drop visual feedback
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            }, false);
        });

        if (btnChange) {
            btnChange.addEventListener("click", function () {
                fileInput.click();
            });
        }

        function handleFileSelect(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewFilename.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                    previewContainer.style.display = "block";
                    dropzone.style.display = "none";
                };
                reader.readAsDataURL(file);
            }
        }
    }
});
</script>
</body>
</html>

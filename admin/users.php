<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

// Memastikan hanya Super Admin yang bisa mengakses halaman ini
check_role(['super_admin']);

$error = '';
$success = '';

// Tambah Admin Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_admin'])) {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass_raw = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($pass_raw)) {
        $error = "Semua bidang formulir admin wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($pass_raw) < 6) {
        $error = "Password admin minimal 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $error = "Email sudah digunakan oleh user lain!";
        } else {
            $pass = password_hash($pass_raw, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, 'admin')");
                $stmt->execute([':nama' => $nama, ':email' => $email, ':pass' => $pass]);
                $success = "Admin baru berhasil ditambahkan!";
            } catch (PDOException $e) {
                $error = "Gagal menambahkan admin: " . $e->getMessage();
            }
        }
    }
}

// Hapus User
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    // Cegah menghapus diri sendiri atau super_admin lain
    if ($id_hapus === (int)$_SESSION['user_id']) {
        $error = "Anda tidak dapat menghapus akun Anda sendiri!";
    } else {
        // Hapus file gambar proyek milik user ini terlebih dahulu
        $stmt_img = $pdo->prepare("SELECT gambar FROM projects WHERE user_id = :id");
        $stmt_img->execute([':id' => $id_hapus]);
        $user_imgs = $stmt_img->fetchAll();
        foreach ($user_imgs as $img) {
            if (!empty($img['gambar']) && file_exists('../uploads/' . $img['gambar'])) {
                unlink('../uploads/' . $img['gambar']);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'super_admin'");
        $stmt->execute([':id' => $id_hapus]);
        header('Location: users.php');
        exit();
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, id ASC")->fetchAll();

// Data User untuk Navbar Modern
$currentUser = get_logged_user($pdo);
$user_nama = htmlspecialchars($currentUser['nama'] ?? $_SESSION['nama'] ?? 'Super Admin');
$user_role = htmlspecialchars($currentUser['role'] ?? $_SESSION['role'] ?? 'super_admin');
$user_foto = $currentUser['foto'] ?? null;

$initials = '';
$words = preg_split('/\s+/', trim($user_nama));
foreach (array_slice($words, 0, 2) as $w) {
    if (!empty($w)) $initials .= strtoupper(mb_substr($w, 0, 1));
}
if (empty($initials)) $initials = 'SA';

$role_badge_class = 'role-badge-' . $user_role;
$role_title = ucwords(str_replace('_', ' ', $user_role));
$role_icon = '👑';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Super Admin</title>
    <!-- Modern Typography Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS Base, Navbar, Form, dan Admin -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<!-- Modern Glassmorphism Navbar -->
<nav class="navbar" id="mainNavbar">
    <!-- Brand Info -->
    <a href="../index.php" class="navbar-brand-link" title="Kembali ke Beranda">
        <div class="brand-logo-frame">
            <img src="../assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
        </div>
        <div class="brand-text-col">
            <h1 class="brand-main-title">Stella <span class="brand-gradient-text">Etalase Digital</span></h1>
            <div class="brand-meta-row">
                <span class="brand-badge-pill">
                    <span class="brand-live-dot"></span> PANEL ADMIN
                </span>
                <span class="brand-page-tag">&bull; Manajemen Users</span>
            </div>
        </div>
    </a>

    <!-- Desktop Menu & User Actions -->
    <div class="navbar-menu-group">
        <!-- User Capsule Link ke Profil -->
        <a href="../profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" title="Buka Profil Saya" style="text-decoration: none;">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('../uploads/avatars/' . $user_foto)): ?>
                    <img src="../uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>">
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

        <a href="../profile.php" class="nav-btn btn-nav-secondary" title="Kelola Profil & Foto">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profil Saya</span>
        </a>

        <a href="../index.php" class="nav-btn btn-nav-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali ke Galeri</span>
        </a>

        <a href="../auth/logout.php" class="nav-btn btn-nav-danger" title="Keluar">
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
        <a href="../profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" style="text-decoration: none;">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('../uploads/avatars/' . $user_foto)): ?>
                    <img src="../uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>">
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

        <a href="../profile.php" class="nav-btn btn-nav-secondary">
            <span>👤 Profil Saya</span>
        </a>

        <a href="../index.php" class="nav-btn btn-nav-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Kembali ke Galeri</span>
        </a>

        <a href="../auth/logout.php" class="nav-btn btn-nav-danger">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Logout Akun</span>
        </a>
    </div>
</nav>


<div style="max-width: 1000px; margin: 30px auto; padding: 0 20px;">
    <?php if (!empty($error)): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; font-size: 14px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; font-size: 14px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="max-width: 100%; margin: 0 0 30px 0;">
        <h2>Tambah Admin Baru</h2>
        <form method="POST" action="users.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="nama">Nama Admin</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama Lengkap Admin" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Email Admin</label>
                    <input type="email" id="email" name="email" placeholder="admin@stella.sch.id" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" name="tambah_admin" class="btn btn-primary" style="padding: 10px 20px;">+ Tambah Admin</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <h2 style="font-size: 18px; margin-bottom: 15px; color: #333;">Daftar Pengguna Terdaftar</h2>
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th style="width: 140px;">Role</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nama']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge-role badge-<?= htmlspecialchars($u['role']) ?>">
                            <?= htmlspecialchars(str_replace('_', ' ', $u['role'])) ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <?php if($u['role'] !== 'super_admin' && $u['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?hapus=<?= $u['id'] ?>" 
                               class="btn btn-danger" 
                               style="padding: 4px 10px; font-size: 12px;" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus user ini beserta seluruh karyanya?')">
                               Hapus
                            </a>
                        <?php else: ?>
                            <span style="color: #adb5bd; font-size: 13px;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../assets/js/navbar.js"></script>
</body>
</html>

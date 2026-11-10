<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

// Memastikan hanya Super Admin dan Admin yang memiliki izin
check_role(['super_admin', 'admin']);

$user = get_logged_user($pdo);
if (!$user) {
    header('Location: ../auth/logout.php');
    exit();
}

$user_role = $user['role'];
$is_super_admin = ($user_role === 'super_admin');

// 1. Hitung Statistik Utama
$stmtStatProjects = $pdo->query("SELECT COUNT(*) AS total FROM projects");
$total_projects = (int) ($stmtStatProjects->fetch()['total'] ?? 0);

$stmtStatLikes = $pdo->query("SELECT COUNT(*) AS total FROM project_likes");
$total_likes = (int) ($stmtStatLikes->fetch()['total'] ?? 0);

$stmtStatSiswa = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'siswa'");
$total_siswa = (int) ($stmtStatSiswa->fetch()['total'] ?? 0);

$stmtStatAdmin = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('admin', 'super_admin')");
$total_admin = (int) ($stmtStatAdmin->fetch()['total'] ?? 0);

// 2. Statistik Karya per Kategori
$stmtKat = $pdo->query("
    SELECT kategori, COUNT(*) AS jumlah 
    FROM projects 
    GROUP BY kategori 
    ORDER BY jumlah DESC
");
$kategori_stats = $stmtKat->fetchAll();

// Kategori paling populer
$top_kategori = !empty($kategori_stats) ? $kategori_stats[0]['kategori'] : 'Belum Ada';

// 3. Top 5 Karya Paling Disukai (Leaderboard)
$stmtTop = $pdo->query("
    SELECT projects.*, users.nama AS pembuat, COALESCE(likes_count.total_likes, 0) AS total_likes 
    FROM projects 
    JOIN users ON projects.user_id = users.id 
    LEFT JOIN (
        SELECT project_id, COUNT(*) AS total_likes 
        FROM project_likes 
        GROUP BY project_id
    ) AS likes_count ON projects.id = likes_count.project_id 
    ORDER BY total_likes DESC, projects.created_at DESC 
    LIMIT 5
");
$top_projects = $stmtTop->fetchAll();

// 4. Daftar Semua Karya dengan Informasi Lengkap untuk Manajemen Admin
$search = trim($_GET['q'] ?? '');
$filter_kat = trim($_GET['kat'] ?? '');

$sqlAll = "
    SELECT projects.*, users.nama AS pembuat, users.email AS pembuat_email, 
           COALESCE(likes_count.total_likes, 0) AS total_likes 
    FROM projects 
    JOIN users ON projects.user_id = users.id 
    LEFT JOIN (
        SELECT project_id, COUNT(*) AS total_likes 
        FROM project_likes 
        GROUP BY project_id
    ) AS likes_count ON projects.id = likes_count.project_id
";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(projects.nama_project LIKE :search OR users.nama LIKE :search OR projects.deskripsi LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($filter_kat) && $filter_kat !== 'all') {
    $conditions[] = "projects.kategori = :kat";
    $params[':kat'] = $filter_kat;
}

if (!empty($conditions)) {
    $sqlAll .= " WHERE " . implode(" AND ", $conditions);
}

$sqlAll .= " ORDER BY projects.created_at DESC";

$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute($params);
$all_projects = $stmtAll->fetchAll();

// Data User untuk Navbar
$user_nama = htmlspecialchars($user['nama'] ?? 'Admin');
$user_foto = $user['foto'] ?? null;
$initials = '';
$words = preg_split('/\s+/', trim($user_nama));
foreach (array_slice($words, 0, 2) as $w) {
    if (!empty($w)) $initials .= strtoupper(mb_substr($w, 0, 1));
}
if (empty($initials)) $initials = $is_super_admin ? 'SA' : 'AD';

$role_title = $is_super_admin ? 'Super Admin' : 'Admin Sekolah';
$role_icon = $is_super_admin ? '👑' : '🛡️';
$role_badge_class = 'role-badge-' . $user_role;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?= $role_title ?> - Stella Etalase Digital</title>
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background: #0b1120; color: #f8fafc; min-height: 100vh;">

<!-- Background Overlay -->
<div class="bg-overlay" style="opacity: 0.65;"></div>

<!-- Navbar Admin Modern -->
<nav class="navbar" id="mainNavbar">
    <a href="index.php" class="navbar-brand-link" title="Dashboard Admin Stella">
        <div class="brand-logo-frame">
            <img src="../assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
        </div>
        <div class="brand-text-col">
            <h1 class="brand-main-title">Stella <span class="brand-gradient-text">Admin Panel</span></h1>
            <div class="brand-meta-row">
                <span class="brand-badge-pill">
                    <span class="brand-live-dot"></span> <?= $role_title ?>
                </span>
            </div>
        </div>
    </a>

    <div class="navbar-menu-group">
        <!-- User Capsule -->
        <a href="../profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" title="Buka Profil Saya" style="text-decoration: none;">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('../uploads/avatars/' . $user_foto)): ?>
                    <img src="../uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="user-meta-text">
                <span class="user-greeting-label">Akun Administrator,</span>
                <span class="user-name-label"><?= $user_nama ?></span>
            </div>
            <span class="role-badge-pill <?= $role_badge_class ?>">
                <?= $role_icon ?> <?= $role_title ?>
            </span>
        </a>

        <!-- Action Links -->
        <a href="../index.php" class="nav-btn btn-nav-secondary" title="Kembali ke Beranda Utama Siswa">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Etalase Siswa</span>
        </a>

        <a href="../katalog.php" class="nav-btn btn-nav-secondary" title="Lihat Halaman Mode Pengunjung">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <span>Mode Publik</span>
        </a>

        <a href="../project_tambah.php" class="nav-btn btn-nav-primary" title="Tambah Karya Baru">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ Unggah Karya</span>
        </a>

        <?php if ($is_super_admin): ?>
            <a href="users.php" class="nav-btn btn-nav-warning" title="Kelola Akun Admin & Siswa">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Kelola Users</span>
            </a>
        <?php endif; ?>

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
        <a href="../index.php" class="nav-btn btn-nav-secondary"><span>🏠 Etalase Siswa</span></a>
        <a href="../katalog.php" class="nav-btn btn-nav-secondary"><span>🌐 Mode Publik</span></a>
        <a href="../project_tambah.php" class="nav-btn btn-nav-primary"><span>+ Tambah Karya</span></a>
        <?php if ($is_super_admin): ?>
            <a href="users.php" class="nav-btn btn-nav-warning"><span>👥 Kelola Manajemen Users</span></a>
        <?php endif; ?>
        <a href="../profile.php" class="nav-btn btn-nav-secondary"><span>👤 Profil Saya</span></a>
        <a href="../auth/logout.php" class="nav-btn btn-nav-danger"><span>🚪 Logout</span></a>
    </div>
</nav>

<main class="container" style="max-width: 1280px; margin: 32px auto 80px;">

    <!-- Admin Welcome Header Card -->
    <div class="admin-welcome-banner">
        <div class="admin-welcome-left">
            <div class="admin-role-tag <?= $is_super_admin ? 'tag-superadmin' : 'tag-admin' ?>">
                <span><?= $role_icon ?> <?= $role_title ?> PANEL</span>
            </div>
            <h1 class="admin-welcome-title">Selamat Datang, <?= $user_nama ?> 👋</h1>
            <p class="admin-welcome-sub">Pantau seluruh statistik inovasi karya, aktivitas apresiasi siswa, dan kelola integritas katalog Stella Etalase Digital.</p>
        </div>

        <div class="admin-welcome-actions">
            <?php if ($is_super_admin): ?>
                <a href="users.php" class="btn-admin-action btn-admin-accent">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                    <span>Kelola Pengguna</span>
                </a>
            <?php endif; ?>
            <a href="../project_tambah.php" class="btn-admin-action btn-admin-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Unggah Karya Baru</span>
            </a>
            <a href="../katalog.php" target="_blank" class="btn-admin-action btn-admin-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                <span>Buka Etalase Publik</span>
            </a>
        </div>
    </div>

    <!-- 4 Top Metric Analytics Cards -->
    <div class="admin-metrics-grid">
        <!-- Metric 1: Total Karya -->
        <div class="metric-card metric-card-red">
            <div class="metric-card-content">
                <span class="metric-label">TOTAL KARYA SISWA</span>
                <div class="metric-value"><?= number_format($total_projects) ?></div>
                <div class="metric-footer">
                    <span>Terpublikasi di etalase</span>
                </div>
            </div>
            <div class="metric-icon-box">🚀</div>
        </div>

        <!-- Metric 2: Total Likes -->
        <div class="metric-card metric-card-rose">
            <div class="metric-card-content">
                <span class="metric-label">TOTAL APRESIASI / LIKE</span>
                <div class="metric-value"><?= number_format($total_likes) ?></div>
                <div class="metric-footer">
                    <span>Interaksi suka dari pengguna</span>
                </div>
            </div>
            <div class="metric-icon-box">❤️</div>
        </div>

        <!-- Metric 3: Total Siswa -->
        <div class="metric-card metric-card-blue">
            <div class="metric-card-content">
                <span class="metric-label">TOTAL SISWA TERDAFTAR</span>
                <div class="metric-value"><?= number_format($total_siswa) ?></div>
                <div class="metric-footer">
                    <span>Kreator & talenta muda</span>
                </div>
            </div>
            <div class="metric-icon-box">🎓</div>
        </div>

        <!-- Metric 4: Total Admin / Populer -->
        <div class="metric-card metric-card-amber">
            <div class="metric-card-content">
                <span class="metric-label">KATEGORI TERPOPULER</span>
                <div class="metric-value" style="font-size: 20px; font-weight: 800; line-height: 1.3; margin: 4px 0 6px;">
                    <?= htmlspecialchars($top_kategori) ?>
                </div>
                <div class="metric-footer">
                    <span><?= $total_admin ?> Pengelola Sistem Aktif</span>
                </div>
            </div>
            <div class="metric-icon-box">🏆</div>
        </div>
    </div>

    <!-- Grid 2 Kolom: Distribusi Kategori & Top 5 Leaderboard -->
    <div class="admin-middle-grid">
        <!-- Distribusi Kategori -->
        <div class="admin-panel-card">
            <div class="admin-panel-header">
                <div>
                    <h3 class="admin-panel-title">📊 Distribusi Bidang Kategori</h3>
                    <p class="admin-panel-desc">Komposisi karya siswa berdasarkan kategori teknologi.</p>
                </div>
            </div>

            <div class="category-breakdown-list">
                <?php if (empty($kategori_stats)): ?>
                    <p style="color: #94a3b8; font-size: 13.5px; text-align: center; padding: 20px 0;">Belum ada data karya terunggah.</p>
                <?php else: ?>
                    <?php foreach ($kategori_stats as $ks): 
                        $pct = ($total_projects > 0) ? round(($ks['jumlah'] / $total_projects) * 100) : 0;
                        $kmeta = get_category_info($ks['kategori']);
                    ?>
                        <div class="cat-progress-item">
                            <div class="cat-progress-header">
                                <span class="cat-progress-name">
                                    <span><?= $kmeta['icon'] ?></span>
                                    <b><?= htmlspecialchars($ks['kategori']) ?></b>
                                </span>
                                <span class="cat-progress-count">
                                    <b><?= $ks['jumlah'] ?></b> Karya (<?= $pct ?>%)
                                </span>
                            </div>
                            <div class="cat-progress-bar-bg">
                                <div class="cat-progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Leaderboard Top 5 Karya -->
        <div class="admin-panel-card">
            <div class="admin-panel-header">
                <div>
                    <h3 class="admin-panel-title">🔥 Top 5 Karya Terfavorit</h3>
                    <p class="admin-panel-desc">Karya yang menerima apresiasi like terbanyak dari komunitas.</p>
                </div>
            </div>

            <div class="leaderboard-list">
                <?php if (empty($top_projects)): ?>
                    <p style="color: #94a3b8; font-size: 13.5px; text-align: center; padding: 20px 0;">Belum ada karya yang di-like.</p>
                <?php else: ?>
                    <?php 
                    $rank = 1;
                    foreach ($top_projects as $tp): 
                        $rank_badge = ($rank === 1) ? '🥇' : (($rank === 2) ? '🥈' : (($rank === 3) ? '🥉' : '#' . $rank));
                    ?>
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank"><?= $rank_badge ?></div>
                            <div class="leaderboard-thumb">
                                <img src="../uploads/<?= htmlspecialchars($tp['gambar']) ?>" alt="" onerror="this.src='../assets/default_project.svg';">
                            </div>
                            <div class="leaderboard-info">
                                <h4 class="leaderboard-title"><?= htmlspecialchars($tp['nama_project']) ?></h4>
                                <div class="leaderboard-meta">
                                    <span>👤 <?= htmlspecialchars($tp['pembuat']) ?></span>
                                    <span>•</span>
                                    <span>🏷️ <?= htmlspecialchars($tp['kategori'] ?? 'Web Design') ?></span>
                                </div>
                            </div>
                            <div class="leaderboard-likes">
                                ❤️ <b><?= (int) $tp['total_likes'] ?></b>
                            </div>
                        </div>
                    <?php 
                        $rank++;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Table: Manajemen Karya Siswa -->
    <div class="admin-panel-card" style="margin-top: 26px;">
        <div class="admin-panel-header" style="flex-wrap: wrap; gap: 14px;">
            <div>
                <h3 class="admin-panel-title">📁 Manajemen Seluruh Karya Siswa</h3>
                <p class="admin-panel-desc">Total <?= count($all_projects) ?> karya terdaftar. Admin dapat meninjau, mengedit, atau menghapus karya.</p>
            </div>

            <!-- Filter & Search Toolbar -->
            <form method="GET" class="admin-table-toolbar">
                <div class="admin-search-wrap">
                    <input type="text" name="q" class="admin-search-input" placeholder="Cari karya atau pembuat..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <select name="kat" class="admin-select-input" onchange="this.form.submit()">
                    <option value="all">Semua Kategori</option>
                    <?php 
                    $catList = get_project_categories();
                    foreach ($catList as $cName => $cData): 
                    ?>
                        <option value="<?= htmlspecialchars($cName) ?>" <?= ($filter_kat === $cName) ? 'selected' : '' ?>>
                            <?= $cData['icon'] ?> <?= htmlspecialchars($cName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-admin-action btn-admin-primary" style="padding: 0 16px; height: 42px;">
                    Cari
                </button>
                <?php if (!empty($search) || !empty($filter_kat)): ?>
                    <a href="index.php" class="btn-admin-action btn-admin-ghost" style="padding: 0 14px; height: 42px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-table-responsive">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 80px;">Cover</th>
                        <th>Judul Karya & Link</th>
                        <th>Kategori</th>
                        <th>Pembuat</th>
                        <th style="text-align: center;">Likes</th>
                        <th>Tanggal Rilis</th>
                        <th style="text-align: center; width: 140px;">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_projects)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                                Tidak ada data karya yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($all_projects as $row): 
                            $row_kat_info = get_category_info($row['kategori'] ?? 'Web Design');
                        ?>
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #64748b;"><?= $no++ ?></td>
                                <td>
                                    <div class="table-img-frame">
                                        <img src="../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="" onerror="this.src='../assets/default_project.svg';">
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; color: #0f172a; margin-bottom: 3px; font-size: 14px;">
                                        <?= htmlspecialchars($row['nama_project']) ?>
                                    </div>
                                    <a href="<?= htmlspecialchars($row['link_project']) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 12px; color: #dc2626; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                                        <span>Buka Link Demo</span>
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                                    </a>
                                </td>
                                <td>
                                    <span class="admin-cat-pill <?= $row_kat_info['class'] ?>">
                                        <?= $row_kat_info['icon'] ?> <?= htmlspecialchars($row['kategori'] ?? 'Web Design') ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['pembuat']) ?></div>
                                    <div style="font-size: 11.5px; color: #64748b;"><?= htmlspecialchars($row['pembuat_email'] ?? '') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="table-likes-badge">
                                        ❤️ <?= (int) $row['total_likes'] ?>
                                    </span>
                                </td>
                                <td style="font-size: 12.5px; color: #64748b; white-space: nowrap;">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <a href="../project_edit.php?id=<?= $row['id'] ?>" class="btn-table-action btn-table-edit" title="Edit Karya">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                            Edit
                                        </a>
                                        <a href="../project_hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus karya ini?')" class="btn-table-action btn-table-delete" title="Hapus Karya">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="../assets/js/navbar.js"></script>
</body>
</html>

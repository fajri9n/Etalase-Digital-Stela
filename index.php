<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

// Ambil data user yang sedang login
$user = get_logged_user($pdo);
if (!$user) {
    header('Location: auth/logout.php');
    exit();
}

// Ambil semua karya beserta nama & foto pembuat
$stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat, users.foto AS pembuat_foto 
                     FROM projects 
                     JOIN users ON projects.user_id = users.id 
                     ORDER BY projects.created_at DESC");
$projects = $stmt->fetchAll();

// Data User untuk Navbar Modern
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

$notice = '';
if (isset($_GET['success'])) {
    $notice = "Karya baru berhasil dipublikasikan ke etalase!";
} elseif (isset($_GET['updated'])) {
    $notice = "Perubahan karya berhasil disimpan!";
} elseif (isset($_GET['deleted'])) {
    $notice = "Karya berhasil dihapus dari etalase.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stella Etalase Digital - Katalog Karya Siswa</title>
    <!-- Modern Typography Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS Base, Navbar & Main -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- Background Foto Gedung Sekolah SMK Stella dengan Efek Overlay Gelap -->
<div class="bg-overlay"></div>

<!-- Modern Glassmorphism Navbar -->
<nav class="navbar" id="mainNavbar">
    <!-- Brand Info (Logo + Title + Status) -->
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
            </div>
        </div>
    </a>

    <!-- Desktop Menu & User Actions -->
    <div class="navbar-menu-group">
        <!-- User Capsule Link ke Profil -->
        <a href="profile.php" class="navbar-user-capsule user-capsule-<?= $user_role ?>" title="Buka Profil Saya" style="text-decoration: none;">
            <div class="user-avatar-circle">
                <?php if (!empty($user_foto) && file_exists('uploads/avatars/' . $user_foto)): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>">
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

        <!-- Action Buttons -->
        <a href="profile.php" class="nav-btn btn-nav-secondary" title="Kelola Profil & Foto">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profil Saya</span>
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

        <a href="auth/logout.php" class="nav-btn btn-nav-danger" title="Keluar dari Akun">
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
                    <img src="uploads/avatars/<?= htmlspecialchars($user_foto) ?>" alt="<?= $user_nama ?>">
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
            <span>👤 Halaman Profil & Foto</span>
        </a>
        <a href="project_tambah.php" class="nav-btn btn-nav-primary">
            <span>+ Tambah Karya Digital</span>
        </a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="nav-btn btn-nav-warning">
                <span>🛡️ Kelola Manajemen Users</span>
            </a>
        <?php endif; ?>
        <a href="auth/logout.php" class="nav-btn btn-nav-danger">
            <span>🚪 Logout Akun</span>
        </a>
    </div>
</nav>

<main class="container">

    <!-- Flash Notice Notification -->
    <?php if (!empty($notice)): ?>
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 14px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; font-size: 13.5px; font-weight: 600; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.1);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span><?= htmlspecialchars($notice) ?></span>
            </div>
            <a href="index.php" style="color: #065f46; text-decoration: none; font-size: 16px; font-weight: 700;">&times;</a>
        </div>
    <?php endif; ?>

    <!-- Showcase Banner Header -->
    <div class="showcase-banner">
        <div class="showcase-banner-text">
            <div class="showcase-banner-badge">
                <span class="badge-dot"></span>
                <span>KATALOG INOVASI DIGITAL</span>
            </div>
            <h1>Katalog Karya Digital Siswa Stella</h1>
            <p>Eksplorasi ragam inovasi, portofolio web, dan proyek teknologi unggulan karya siswa berbakat SMK Stella Maris.</p>
        </div>

        <div class="showcase-toolbar">
            <div class="search-box-wrapper">
                <span class="search-box-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="searchInput" class="search-box-input" placeholder="Cari karya atau nama siswa..." onkeyup="filterProjects()">
            </div>

            <div class="total-karya-pill">
                <span>Total:</span>
                <b id="projectCount"><?= count($projects) ?></b>
                <span>Karya</span>
            </div>

            <a href="project_tambah.php" class="nav-btn btn-nav-primary" style="height: 46px; border-radius: 12px; padding: 0 18px;">
                <span>+ Unggah Karya</span>
            </a>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(14px); padding: 70px 24px; text-align: center; border-radius: 22px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);">
            <div style="font-size: 52px; margin-bottom: 14px;">🎨</div>
            <h3 style="color: #0f172a; margin-bottom: 8px; font-size: 20px; font-weight: 800;">Belum Ada Karya yang Diunggah</h3>
            <p style="color: #64748b; margin-bottom: 25px; font-size: 14px; max-width: 460px; margin-left: auto; margin-right: auto;">
                Jadilah siswa pertama yang memamerkan karya digital dan proyek inovasimu di etalase ini!
            </p>
            <a href="project_tambah.php" class="btn btn-primary" style="padding: 12px 26px; border-radius: 12px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                + Unggah Karya Pertama Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="grid-projects" id="projectsGrid">
            <?php foreach($projects as $p): 
                $author_initial = strtoupper(mb_substr(trim($p['pembuat']), 0, 1));
                $author_foto = $p['pembuat_foto'] ?? null;
            ?>
                <div class="card-project"
                     data-project-title="<?= htmlspecialchars($p['nama_project'], ENT_QUOTES) ?>"
                     data-project-author="<?= htmlspecialchars($p['pembuat'], ENT_QUOTES) ?>"
                     data-project-author-photo="<?= !empty($author_foto) ? 'uploads/avatars/' . htmlspecialchars($author_foto, ENT_QUOTES) : '' ?>"
                     data-project-author-initial="<?= $author_initial ?>"
                     data-project-date="<?= date('d M Y', strtotime($p['created_at'])) ?>"
                     data-project-image="uploads/<?= htmlspecialchars($p['gambar'], ENT_QUOTES) ?>"
                     data-project-description="<?= htmlspecialchars($p['deskripsi'], ENT_QUOTES) ?>"
                     data-project-link="<?= htmlspecialchars($p['link_project'], ENT_QUOTES) ?>"
                     data-title="<?= strtolower(htmlspecialchars($p['nama_project'])) ?>"
                     data-author="<?= strtolower(htmlspecialchars($p['pembuat'])) ?>"
                     data-desc="<?= strtolower(htmlspecialchars($p['deskripsi'])) ?>">
                    
                    <!-- Cover Image -->
                    <div class="card-img-wrapper">
                        <img src="uploads/<?= htmlspecialchars($p['gambar']) ?>" 
                             alt="<?= htmlspecialchars($p['nama_project']) ?>" 
                             class="card-img-top" 
                             onerror="this.onerror=null; this.src='assets/default_project.svg';">
                        <span class="card-floating-date">
                            📅 <?= date('d M Y', strtotime($p['created_at'])) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Author Meta with Photo -->
                        <div class="card-author-row">
                            <div class="author-mini-avatar">
                                <?php if (!empty($author_foto) && file_exists('uploads/avatars/' . $author_foto)): ?>
                                    <img src="uploads/avatars/<?= htmlspecialchars($author_foto) ?>" alt="<?= htmlspecialchars($p['pembuat']) ?>">
                                <?php else: ?>
                                    <div style="background: linear-gradient(135deg, #0f172a 0%, #334155 100%); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <?= $author_initial ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="author-name-text">
                                Oleh: <b><?= htmlspecialchars($p['pembuat']) ?></b>
                            </div>
                        </div>

                        <!-- Project Title -->
                        <h3 class="card-title"><?= htmlspecialchars($p['nama_project']) ?></h3>

                        <!-- Truncated Description -->
                        <p class="card-text"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
                        
                        <!-- Card Footer Links & Actions -->
                        <div class="card-footer-row" onclick="event.stopPropagation();">
                            <a href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer" class="card-link-btn">
                                <span>Kunjungi Web</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="7" y1="17" x2="17" y2="7"></line>
                                    <polyline points="7 7 17 7 17 17"></polyline>
                                </svg>
                            </a>
                            
                            <!-- Hak Akses Edit/Hapus: Owner / Admin / Super Admin -->
                            <?php if($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $p['user_id']): ?>
                                <div class="card-actions-group">
                                    <a href="project_edit.php?id=<?= $p['id'] ?>" class="btn-card-edit" title="Edit Karya">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        Edit
                                    </a>
                                    <a href="project_hapus.php?id=<?= $p['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus karya ini?')" class="btn-card-delete" title="Hapus Karya">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResultsMessage" style="display: none; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(14px); padding: 45px; text-align: center; border-radius: 20px; margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div style="font-size: 38px; margin-bottom: 8px;">🔍</div>
            <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Karya Tidak Ditemukan</h3>
            <p style="color: #64748b; font-size: 13.5px; margin: 0;">Tidak ada karya yang cocok dengan kata kunci pencarian Anda. Coba kata kunci lainnya.</p>
        </div>
    <?php endif; ?>
</main>

<!-- =====================================================
     PROJECT DETAIL MODAL
===================================================== -->
<div class="project-modal" id="projectModal">
    <div class="project-modal-overlay" id="projectModalOverlay"></div>

    <div class="project-modal-box">
        <button type="button" class="project-modal-close" id="closeProjectModal" aria-label="Tutup modal">
            &times;
        </button>

        <div class="project-modal-image">
            <img id="modalProjectImage" src="" alt="Project">
        </div>

        <div class="project-modal-content">
            <span class="modal-label">DETAIL KARYA DIGITAL</span>
            <h2 id="modalProjectTitle">Nama Project</h2>

            <div class="modal-project-meta">
                <span>
                    👤 Pembuat: <b id="modalProjectAuthor">Pembuat</b>
                </span>
                <span>
                    📅 Rilis: <b id="modalProjectDate">Tanggal</b>
                </span>
            </div>

            <div class="modal-divider"></div>

            <p id="modalProjectDescription">Deskripsi project.</p>

            <a id="modalProjectLink" href="#" target="_blank" rel="noopener noreferrer" class="modal-project-button">
                <span>Buka Demo / Link Karya</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- =====================================================
     SCRIPTS
===================================================== -->
<script src="assets/js/navbar.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    /* Search & Filter */
    window.filterProjects = function () {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase().trim();
        const cards = document.querySelectorAll("#projectsGrid .card-project");
        const countBadge = document.getElementById("projectCount");
        const noResults = document.getElementById("noResultsMessage");
        let visibleCount = 0;

        cards.forEach(function (card) {
            const title = card.getAttribute("data-title") || "";
            const author = card.getAttribute("data-author") || "";
            const desc = card.getAttribute("data-desc") || "";

            if (title.includes(filter) || author.includes(filter) || desc.includes(filter)) {
                card.style.display = "flex";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        if (countBadge) countBadge.textContent = visibleCount;
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && cards.length > 0) ? "block" : "none";
        }
    };

    /* Modal Controller */
    const modal = document.getElementById("projectModal");
    const overlay = document.getElementById("projectModalOverlay");
    const closeBtn = document.getElementById("closeProjectModal");
    const modalImage = document.getElementById("modalProjectImage");
    const modalTitle = document.getElementById("modalProjectTitle");
    const modalAuthor = document.getElementById("modalProjectAuthor");
    const modalDate = document.getElementById("modalProjectDate");
    const modalDesc = document.getElementById("modalProjectDescription");
    const modalLink = document.getElementById("modalProjectLink");

    function openModal(card) {
        modalImage.src = card.getAttribute("data-project-image");
        modalTitle.textContent = card.getAttribute("data-project-title");
        modalAuthor.textContent = card.getAttribute("data-project-author");
        modalDate.textContent = card.getAttribute("data-project-date");
        modalDesc.textContent = card.getAttribute("data-project-description");
        modalLink.href = card.getAttribute("data-project-link");

        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }

    // Attach click events to cards
    document.querySelectorAll(".card-project").forEach(card => {
        card.addEventListener("click", function (e) {
            if (e.target.closest("a") || e.target.closest("button")) {
                return;
            }
            openModal(this);
        });
    });

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (overlay) overlay.addEventListener("click", closeModal);

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modal && modal.classList.contains("active")) {
            closeModal();
        }
    });
});
</script>
</body>
</html>

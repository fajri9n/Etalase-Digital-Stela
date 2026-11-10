<?php
session_start();
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

// Cek apakah ada user yang sedang login
$current_user_id = $_SESSION['user_id'] ?? 0;
$is_logged_in = !empty($current_user_id);

// Ambil semua karya beserta nama, foto pembuat, total like, dan status like jika user login
try {
    $stmt = $pdo->prepare("
        SELECT 
            projects.*, 
            users.nama AS pembuat, 
            users.foto AS pembuat_foto,
            COALESCE(likes_count.total_likes, 0) AS total_likes,
            CASE WHEN user_like.id IS NOT NULL THEN 1 ELSE 0 END AS is_liked
        FROM projects 
        JOIN users ON projects.user_id = users.id 
        LEFT JOIN (
            SELECT project_id, COUNT(*) AS total_likes 
            FROM project_likes 
            GROUP BY project_id
        ) AS likes_count ON projects.id = likes_count.project_id
        LEFT JOIN project_likes AS user_like ON projects.id = user_like.project_id AND user_like.user_id = :uid
        ORDER BY projects.created_at DESC
    ");
    $stmt->execute([':uid' => $current_user_id]);
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $stmt = $pdo->query("
        SELECT projects.*, users.nama AS pembuat, users.foto AS pembuat_foto, 0 AS total_likes, 0 AS is_liked 
        FROM projects 
        JOIN users ON projects.user_id = users.id 
        ORDER BY projects.created_at DESC
    ");
    $projects = $stmt->fetchAll();
}

// Hitung total like & kategori
$total_all_likes = 0;
$category_counts = ['all' => count($projects)];
foreach ($projects as $pr) {
    $total_all_likes += (int) ($pr['total_likes'] ?? 0);
    $c_name = $pr['kategori'] ?? 'Web Design';
    $category_counts[$c_name] = ($category_counts[$c_name] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etalase Digital SMK Telkom Lampung - Panggung Karya & Inovasi Siswa</title>
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/chatbot.css">
</head>
<body>

<!-- Background Foto Gedung Sekolah SMK Stella dengan Efek Overlay Gelap -->
<div class="bg-overlay"></div>

<!-- Modern Navbar untuk Pengunjung -->
<nav class="navbar" id="mainNavbar">
    <a href="katalog.php" class="navbar-brand-link" title="Stella Etalase Digital">
        <div class="brand-logo-frame">
            <img src="assets/img/logo-ts2.png" alt="Logo Stella" class="brand-logo-img" onerror="this.style.display='none';">
        </div>
        <div class="brand-text-col">
            <h1 class="brand-main-title">Stella <span class="brand-gradient-text">Etalase Digital</span></h1>
            <div class="brand-meta-row">
                <span class="brand-badge-pill">
                    <span class="brand-live-dot"></span> SMK STELLA MARIS • PUBLIK
                </span>
            </div>
        </div>
    </a>

    <div class="navbar-menu-group">
        <?php if ($is_logged_in): ?>
            <!-- User is logged in -->
            <a href="index.php" class="nav-btn btn-nav-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Halaman Siswa</span>
            </a>
            <a href="profile.php" class="nav-btn btn-nav-secondary">
                <span>Profil Saya</span>
            </a>
            <a href="auth/logout.php" class="nav-btn btn-nav-danger">
                <span>Logout</span>
            </a>
        <?php else: ?>
            <!-- Guest Visitor -->
            <a href="auth/login.php" class="nav-btn btn-nav-secondary" title="Masuk ke Akun Siswa">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <span>Masuk Akun</span>
            </a>
            <a href="auth/register.php" class="nav-btn btn-nav-primary" title="Daftar Akun Siswa Baru">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Daftar Akun Siswa</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="navbar-toggle-btn" id="navbarToggle" aria-label="Menu Navigasi">
        <span class="navbar-toggle-bar"></span>
        <span class="navbar-toggle-bar"></span>
        <span class="navbar-toggle-bar"></span>
    </button>

    <!-- Mobile Drawer Panel -->
    <div class="navbar-mobile-drawer" id="navbarMobileDrawer">
        <?php if ($is_logged_in): ?>
            <a href="index.php" class="nav-btn btn-nav-primary"><span>🏠 Halaman Siswa</span></a>
            <a href="profile.php" class="nav-btn btn-nav-secondary"><span>👤 Profil Saya</span></a>
            <a href="auth/logout.php" class="nav-btn btn-nav-danger"><span>🚪 Logout</span></a>
        <?php else: ?>
            <a href="auth/login.php" class="nav-btn btn-nav-secondary"><span>🔑 Masuk ke Akun</span></a>
            <a href="auth/register.php" class="nav-btn btn-nav-primary"><span>✨ Daftar Akun Siswa</span></a>
        <?php endif; ?>
    </div>
</nav>

<main class="container">

    <!-- Public Showcase Hero Banner -->
    <div class="showcase-banner">
        <div class="showcase-banner-text" style="max-width: 660px;">
            <div class="showcase-banner-badge">
                <span class="badge-dot"></span>
                <span>PANGGUNG INOVASI & TALENTA DIGITAL</span>
            </div>
            <h1>Pameran Karya Siswa SMK Stella Maris</h1>
            <p>Jelajahi ragam portofolio website, prototipe UI/UX, aplikasi mobile, desain grafis, dan proyek Internet of Things unggulan karya siswa berbakat.</p>
        </div>

        <div class="showcase-toolbar">
            <div class="search-box-wrapper">
                <span class="search-box-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input type="text" id="searchInput" class="search-box-input" placeholder="Cari karya atau nama pembuat..." onkeyup="filterProjects()">
            </div>

            <div class="total-karya-pill">
                <span>Total:</span>
                <b id="projectCount"><?= count($projects) ?></b>
                <span>Karya</span>
            </div>

            <?php if (!$is_logged_in): ?>
                <a href="auth/register.php" class="nav-btn btn-nav-primary" style="height: 46px; border-radius: 12px; padding: 0 18px;">
                    <span>+ Ikut Pamerkan Karya</span>
                </a>
            <?php else: ?>
                <a href="project_tambah.php" class="nav-btn btn-nav-primary" style="height: 46px; border-radius: 12px; padding: 0 18px;">
                    <span>+ Unggah Karya</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Filter Bar Container -->
    <div class="category-filter-bar">
        <div class="category-filter-header">
            <div class="category-header-title">
                <span class="cat-dot"></span>
                <span>KATEGORI KARYA DIGITAL</span>
                <span class="category-header-desc">• Pilih bidang untuk menyaring inovasi</span>
            </div>
            <div class="category-scroll-hint">
                Geser untuk kategori lainnya &rarr;
            </div>
        </div>
        <div class="category-filter-scroll">
            <button type="button" class="category-tab active" data-category="all" onclick="filterByCategory('all')">
                <span class="cat-tab-icon">🔥</span>
                <span class="cat-tab-label">Semua Karya</span>
                <span class="cat-tab-count"><?= count($projects) ?></span>
            </button>
            <?php 
            $all_categories = get_project_categories();
            foreach ($all_categories as $kat_name => $kat_meta): 
                $count_kat = $category_counts[$kat_name] ?? 0;
            ?>
                <button type="button" class="category-tab" data-category="<?= htmlspecialchars($kat_name) ?>" onclick="filterByCategory('<?= htmlspecialchars($kat_name) ?>')">
                    <span class="cat-tab-icon"><?= $kat_meta['icon'] ?></span>
                    <span class="cat-tab-label"><?= htmlspecialchars($kat_name) ?></span>
                    <?php if ($count_kat > 0): ?>
                        <span class="cat-tab-count"><?= $count_kat ?></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(14px); padding: 70px 24px; text-align: center; border-radius: 22px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);">
            <div style="font-size: 52px; margin-bottom: 14px;">🎨</div>
            <h3 style="color: #0f172a; margin-bottom: 8px; font-size: 20px; font-weight: 800;">Belum Ada Karya yang Diunggah</h3>
            <p style="color: #64748b; margin-bottom: 25px; font-size: 14px; max-width: 460px; margin-left: auto; margin-right: auto;">
                Karya inovasi siswa akan segera ditampilkan di halaman pameran publik ini.
            </p>
            <a href="auth/login.php" class="btn btn-primary" style="padding: 12px 26px; border-radius: 12px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                Masuk untuk Mengunggah Karya
            </a>
        </div>
    <?php else: ?>
        <div class="grid-projects" id="projectsGrid">
            <?php foreach($projects as $p): 
                $author_initial = strtoupper(mb_substr(trim($p['pembuat']), 0, 1));
                $author_foto    = $p['pembuat_foto'] ?? null;
                $project_kat    = (!empty($p['kategori'])) ? $p['kategori'] : 'Web Design';
                $kat_info       = get_category_info($project_kat);
                $is_liked       = !empty($p['is_liked']);
                $total_likes    = (int) ($p['total_likes'] ?? 0);
            ?>
                <div class="card-project"
                     data-project-id="<?= $p['id'] ?>"
                     data-project-title="<?= htmlspecialchars($p['nama_project'], ENT_QUOTES) ?>"
                     data-project-category="<?= htmlspecialchars($project_kat, ENT_QUOTES) ?>"
                     data-project-category-icon="<?= htmlspecialchars($kat_info['icon'], ENT_QUOTES) ?>"
                     data-project-author="<?= htmlspecialchars($p['pembuat'], ENT_QUOTES) ?>"
                     data-project-author-photo="<?= !empty($author_foto) ? 'uploads/avatars/' . htmlspecialchars($author_foto, ENT_QUOTES) : '' ?>"
                     data-project-author-initial="<?= $author_initial ?>"
                     data-project-date="<?= date('d M Y', strtotime($p['created_at'])) ?>"
                     data-project-image="uploads/<?= htmlspecialchars($p['gambar'], ENT_QUOTES) ?>"
                     data-project-description="<?= htmlspecialchars($p['deskripsi'], ENT_QUOTES) ?>"
                     data-project-link="<?= htmlspecialchars($p['link_project'], ENT_QUOTES) ?>"
                     data-project-likes="<?= $total_likes ?>"
                     data-project-is-liked="<?= $is_liked ? '1' : '0' ?>"
                     data-title="<?= strtolower(htmlspecialchars($p['nama_project'])) ?>"
                     data-author="<?= strtolower(htmlspecialchars($p['pembuat'])) ?>"
                     data-category="<?= strtolower(htmlspecialchars($project_kat)) ?>"
                     data-desc="<?= strtolower(htmlspecialchars($p['deskripsi'])) ?>">
                    
                    <!-- Cover Image -->
                    <div class="card-img-wrapper">
                        <img src="uploads/<?= htmlspecialchars($p['gambar']) ?>" 
                             alt="<?= htmlspecialchars($p['nama_project']) ?>" 
                             class="card-img-top" 
                             onerror="this.onerror=null; this.src='assets/default_project.svg';">

                        <!-- Badge Kategori Floating -->
                        <span class="card-floating-category <?= $kat_info['class'] ?>" title="Kategori: <?= htmlspecialchars($project_kat) ?>">
                            <span class="cat-pill-icon"><?= $kat_info['icon'] ?></span>
                            <span><?= htmlspecialchars($project_kat) ?></span>
                        </span>

                        <!-- Date Pill Floating -->
                        <span class="card-floating-date">
                            📅 <?= date('d M Y', strtotime($p['created_at'])) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Author Meta with Photo & Category -->
                        <div class="card-author-row" style="justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 8px;">
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

                            <span class="card-badge-inline <?= $kat_info['class'] ?>" style="padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 700; white-space: nowrap;">
                                <?= $kat_info['icon'] ?> <?= htmlspecialchars($project_kat) ?>
                            </span>
                        </div>

                        <!-- Project Title -->
                        <h3 class="card-title"><?= htmlspecialchars($p['nama_project']) ?></h3>

                        <!-- Truncated Description -->
                        <p class="card-text"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
                        
                        <!-- Card Footer Links & Actions -->
                        <div class="card-footer-row" onclick="event.stopPropagation();">
                            <div class="card-footer-left">
                                <a href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer" class="card-link-btn">
                                    <span>Kunjungi Web</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7"></line>
                                        <polyline points="7 7 17 7 17 17"></polyline>
                                    </svg>
                                </a>
                            </div>

                            <div class="card-footer-right">
                                <!-- Tombol Like Interaktif -->
                                <button type="button" 
                                        class="btn-card-like <?= $is_liked ? 'liked' : '' ?>" 
                                        data-project-id="<?= $p['id'] ?>"
                                        title="<?= $is_liked ? 'Batal Suka' : 'Sukai Karya Ini' ?>"
                                        onclick="handleLikeClick(event, <?= $p['id'] ?>)">
                                    <svg class="heart-icon" viewBox="0 0 24 24" width="16" height="16" fill="<?= $is_liked ? '#ef4444' : 'none' ?>" stroke="<?= $is_liked ? '#ef4444' : 'currentColor' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                    <span class="like-counter"><?= $total_likes ?></span>
                                </button>
                            </div>
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
                    🏷️ Kategori: <b id="modalProjectCategory">Kategori</b>
                </span>
                <span>
                    📅 Rilis: <b id="modalProjectDate">Tanggal</b>
                </span>
            </div>

            <div class="modal-divider"></div>

            <p id="modalProjectDescription">Deskripsi project.</p>

            <div class="modal-actions-row">
                <a id="modalProjectLink" href="#" target="_blank" rel="noopener noreferrer" class="modal-project-button">
                    <span>Buka Demo / Link Karya</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </a>

                <button type="button" class="modal-like-btn" id="modalLikeButton" onclick="handleModalLike()">
                    <svg class="heart-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    <span id="modalLikeCountText">0 Suka</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajakan Login untuk Pengunjung -->
<div class="project-modal" id="loginPromptModal">
    <div class="project-modal-overlay" onclick="closeLoginPrompt()"></div>
    <div style="position: relative; z-index: 10; background: #ffffff; padding: 36px 32px; border-radius: 22px; max-width: 440px; width: 90%; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.35);">
        <div style="font-size: 46px; margin-bottom: 12px;">❤️</div>
        <h3 style="font-size: 19px; font-weight: 800; color: #0f172a; margin: 0 0 8px;">Sukai Karya Ini?</h3>
        <p style="font-size: 13.5px; color: #64748b; line-height: 1.5; margin: 0 0 24px;">
            Silakan masuk atau daftar akun siswa terlebih dahulu untuk memberikan apresiasi like pada karya inovasi teman-temanmu!
        </p>
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a href="auth/login.php" class="nav-btn btn-nav-primary" style="padding: 11px 22px; font-size: 13.5px; border-radius: 10px;">
                Masuk Sekarang
            </a>
            <a href="auth/register.php" class="nav-btn btn-nav-secondary" style="padding: 11px 20px; font-size: 13.5px; border-radius: 10px;">
                Daftar Akun
            </a>
        </div>
        <button type="button" onclick="closeLoginPrompt()" style="margin-top: 16px; background: none; border: none; font-size: 12.5px; color: #94a3b8; cursor: pointer; text-decoration: underline;">
            Nanti Saja
        </button>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/navbar.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const isLoggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;
    let activeCategory = 'all';
    let currentModalProjectId = null;

    /* Filter Projects */
    window.applyFilters = function () {
        const input = document.getElementById("searchInput");
        const filter = input ? input.value.toLowerCase().trim() : "";
        const cards = document.querySelectorAll("#projectsGrid .card-project");
        const countBadge = document.getElementById("projectCount");
        const noResults = document.getElementById("noResultsMessage");
        let visibleCount = 0;

        cards.forEach(function (card) {
            const title = card.getAttribute("data-title") || "";
            const author = card.getAttribute("data-author") || "";
            const desc = card.getAttribute("data-desc") || "";
            const category = card.getAttribute("data-category") || "";

            const matchSearch = (!filter) || title.includes(filter) || author.includes(filter) || desc.includes(filter);
            const matchCategory = (activeCategory === 'all') || (category === activeCategory.toLowerCase());

            if (matchSearch && matchCategory) {
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

    window.filterProjects = function () {
        window.applyFilters();
    };

    window.filterByCategory = function (category) {
        activeCategory = category;

        document.querySelectorAll(".category-tab").forEach(tab => {
            if (tab.getAttribute("data-category") === category) {
                tab.classList.add("active");
            } else {
                tab.classList.remove("active");
            }
        });

        window.applyFilters();
    };

    /* Like Controller */
    window.handleLikeClick = async function (event, projectId) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }

        if (!isLoggedIn) {
            openLoginPrompt();
            return;
        }

        const btn = document.querySelector(`.btn-card-like[data-project-id="${projectId}"]`);
        if (btn) btn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('project_id', projectId);

            const res = await fetch('project_like.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();
            if (data.success) {
                updateLikeUI(projectId, data.is_liked, data.total_likes);
            }
        } catch (err) {
            console.error('Error saat like:', err);
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    window.handleModalLike = function () {
        if (currentModalProjectId) {
            window.handleLikeClick(null, currentModalProjectId);
        }
    };

    function updateLikeUI(projectId, isLiked, totalLikes) {
        const card = document.querySelector(`.card-project[data-project-id="${projectId}"]`);
        if (card) {
            card.setAttribute('data-project-likes', totalLikes);
            card.setAttribute('data-project-is-liked', isLiked ? '1' : '0');
        }

        const cardBtn = document.querySelector(`.btn-card-like[data-project-id="${projectId}"]`);
        if (cardBtn) {
            const heart = cardBtn.querySelector('.heart-icon');
            const counter = cardBtn.querySelector('.like-counter');

            if (isLiked) {
                cardBtn.classList.add('liked');
                if (heart) {
                    heart.setAttribute('fill', '#ef4444');
                    heart.setAttribute('stroke', '#ef4444');
                }
                cardBtn.classList.add('heart-pulse');
                setTimeout(() => cardBtn.classList.remove('heart-pulse'), 400);
            } else {
                cardBtn.classList.remove('liked');
                if (heart) {
                    heart.setAttribute('fill', 'none');
                    heart.setAttribute('stroke', 'currentColor');
                }
            }
            if (counter) counter.textContent = totalLikes;
        }

        if (currentModalProjectId == projectId) {
            const modalBtn = document.getElementById("modalLikeButton");
            const modalText = document.getElementById("modalLikeCountText");
            if (modalBtn) {
                const modalHeart = modalBtn.querySelector('.heart-icon');
                if (isLiked) {
                    modalBtn.classList.add('liked');
                    if (modalHeart) {
                        modalHeart.setAttribute('fill', '#ef4444');
                        modalHeart.setAttribute('stroke', '#ef4444');
                    }
                    modalBtn.classList.add('heart-pulse');
                    setTimeout(() => modalBtn.classList.remove('heart-pulse'), 400);
                } else {
                    modalBtn.classList.remove('liked');
                    if (modalHeart) {
                        modalHeart.setAttribute('fill', 'none');
                        modalHeart.setAttribute('stroke', 'currentColor');
                    }
                }
            }
            if (modalText) modalText.textContent = totalLikes + ' Suka';
        }
    }

    /* Login Prompt Modal */
    window.openLoginPrompt = function () {
        const pModal = document.getElementById("loginPromptModal");
        if (pModal) {
            pModal.classList.add("active");
            document.body.style.overflow = "hidden";
        }
    };

    window.closeLoginPrompt = function () {
        const pModal = document.getElementById("loginPromptModal");
        if (pModal) {
            pModal.classList.remove("active");
            document.body.style.overflow = "";
        }
    };

    /* Modal Detail Controller */
    const modal = document.getElementById("projectModal");
    const overlay = document.getElementById("projectModalOverlay");
    const closeBtn = document.getElementById("closeProjectModal");
    const modalImage = document.getElementById("modalProjectImage");
    const modalTitle = document.getElementById("modalProjectTitle");
    const modalAuthor = document.getElementById("modalProjectAuthor");
    const modalCategory = document.getElementById("modalProjectCategory");
    const modalDate = document.getElementById("modalProjectDate");
    const modalDesc = document.getElementById("modalProjectDescription");
    const modalLink = document.getElementById("modalProjectLink");
    const modalLikeBtn = document.getElementById("modalLikeButton");
    const modalLikeText = document.getElementById("modalLikeCountText");

    function openModal(card) {
        currentModalProjectId = card.getAttribute("data-project-id");

        modalImage.src = card.getAttribute("data-project-image");
        modalTitle.textContent = card.getAttribute("data-project-title");
        modalAuthor.textContent = card.getAttribute("data-project-author");
        
        const catName = card.getAttribute("data-project-category") || "Web Design";
        const catIcon = card.getAttribute("data-project-category-icon") || "🎨";
        if (modalCategory) {
            modalCategory.textContent = catIcon + " " + catName;
        }

        modalDate.textContent = card.getAttribute("data-project-date");
        modalDesc.textContent = card.getAttribute("data-project-description");
        modalLink.href = card.getAttribute("data-project-link");

        const isLiked = card.getAttribute("data-project-is-liked") === "1";
        const totalLikes = parseInt(card.getAttribute("data-project-likes") || "0");

        if (modalLikeBtn) {
            const modalHeart = modalLikeBtn.querySelector('.heart-icon');
            if (isLiked) {
                modalLikeBtn.classList.add('liked');
                if (modalHeart) {
                    modalHeart.setAttribute('fill', '#ef4444');
                    modalHeart.setAttribute('stroke', '#ef4444');
                }
            } else {
                modalLikeBtn.classList.remove('liked');
                if (modalHeart) {
                    modalHeart.setAttribute('fill', 'none');
                    modalHeart.setAttribute('stroke', 'currentColor');
                }
            }
        }
        if (modalLikeText) modalLikeText.textContent = totalLikes + ' Suka';

        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        modal.classList.remove("active");
        document.body.style.overflow = "";
        currentModalProjectId = null;
    }

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
        if (e.key === "Escape") {
            if (modal && modal.classList.contains("active")) closeModal();
            closeLoginPrompt();
        }
    });
});
</script>

<!-- Floating Chatbot Widget -->
<?php include 'chatbot_widget.php'; ?>
<script src="assets/js/chatbot.js"></script>

</body>
</html>

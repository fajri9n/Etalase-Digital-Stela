<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

// Ambil semua karya beserta nama pembuat
$stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat 
                     FROM projects 
                     JOIN users ON projects.user_id = users.id 
                     ORDER BY projects.created_at DESC");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etalase Digital Stella</title>
    <!-- Modern Typography Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Hubungkan CSS Base & CSS Main -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- Background Foto Gedung Sekolah SMK Stella dengan Efek Blur & Overlay Gelap -->
<div class="bg-overlay"></div>

<nav class="navbar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <img src="assets/img/logo-ts2.png" alt="Logo Stella" style="height: 38px; width: auto; object-fit: contain;" onerror="this.style.display='none';">
        <h2 style="margin: 0; font-weight: 800; font-size: 21px; color: #007bff;">
            Etalase Digital Stella
        </h2>
    </div>
    <div class="navbar-user">
        <span style="font-size: 14px; color: #475569;">
            Halo, <b style="color: #0f172a;"><?= htmlspecialchars($_SESSION['nama']) ?></b> 
            <span style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 9999px; text-transform: uppercase;">
                <?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role'])) ?>
            </span>
        </span>
        <a href="project_tambah.php" class="btn btn-primary">+ Tambah Karya</a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="btn btn-warning">Kelola Users</a>
        <?php endif; ?>
        <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="container">
    <!-- Showcase Banner Header -->
    <div class="showcase-banner">
        <div class="showcase-banner-text">
            <h1>Katalog Karya Digital Siswa Stella</h1>
            <p>Eksplorasi ragam inovasi, aplikasi web, dan karya teknologi siswa berbakat SMK Stella.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div class="search-box-wrapper">
                <span class="search-box-icon">🔍</span>
                <input type="text" id="searchInput" class="search-box-input" placeholder="Cari karya atau nama pembuat..." onkeyup="filterProjects()">
            </div>
            <span style="background: rgba(255, 255, 255, 0.9); color: #0f172a; font-weight: 700; font-size: 13px; padding: 10px 16px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); white-space: nowrap;">
                Total: <b id="projectCount" style="color: #007bff;"><?= count($projects) ?></b> Karya
            </span>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 60px 20px; text-align: center; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            <div style="font-size: 48px; margin-bottom: 12px;">🎨</div>
            <h3 style="color: #334155; margin-bottom: 8px; font-size: 18px;">Belum Ada Karya yang Diunggah</h3>
            <p style="color: #64748b; margin-bottom: 25px; font-size: 14px;">Jadilah siswa pertama yang memamerkan karya digitalmu di etalase ini!</p>
            <a href="project_tambah.php" class="btn btn-primary" style="padding: 10px 22px; border-radius: 8px;">+ Unggah Karya Sekarang</a>
        </div>
    <?php else: ?>
        <div class="grid-projects" id="projectsGrid">
            <?php foreach($projects as $p): ?>
                <div class="card-project" 
                     data-title="<?= strtolower(htmlspecialchars($p['nama_project'])) ?>"
                     data-author="<?= strtolower(htmlspecialchars($p['pembuat'])) ?>"
                     data-desc="<?= strtolower(htmlspecialchars($p['deskripsi'])) ?>">
                    
                    <div class="card-img-wrapper">
                        <img src="uploads/<?= htmlspecialchars($p['gambar']) ?>" 
                             alt="<?= htmlspecialchars($p['nama_project']) ?>" 
                             class="card-img-top" 
                             onerror="this.onerror=null; this.src='assets/default_project.svg';">
                    </div>
                    
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($p['nama_project']) ?></h3>
                        <p class="card-author">
                            <span>Oleh: <b><?= htmlspecialchars($p['pembuat']) ?></b></span>
                            <span>&bull;</span>
                            <span><?= date('d M Y', strtotime($p['created_at'])) ?></span>
                        </p>
                        <p class="card-text"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
                        
                        <div style="margin-bottom: 14px;">
                            <a href="<?= htmlspecialchars($p['link_project']) ?>" target="_blank" rel="noopener noreferrer" class="card-link">
                                Lihat Project Tautan &rarr;
                            </a>
                        </div>
                        
                        <!-- Hak Akses Edit/Hapus: Owner / Admin / Super Admin -->
                        <?php if($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $p['user_id']): ?>
                            <div class="card-actions">
                                <a href="project_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning" style="flex: 1; text-align: center;">Edit</a>
                                <a href="project_hapus.php?id=<?= $p['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus karya ini?')" class="btn btn-danger" style="flex: 1; text-align: center;">Hapus</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResultsMessage" style="display: none; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 40px; text-align: center; border-radius: 16px; margin-top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <p style="color: #64748b; font-size: 15px;">Tidak ditemukan karya yang cocok dengan kata kunci pencarian Anda.</p>
        </div>
    <?php endif; ?>
</main>

<script>
function filterProjects() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase().trim();
    const cards = document.querySelectorAll('#projectsGrid .card-project');
    const countBadge = document.getElementById('projectCount');
    const noResults = document.getElementById('noResultsMessage');
    
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.getAttribute('data-title') || '';
        const author = card.getAttribute('data-author') || '';
        const desc = card.getAttribute('data-desc') || '';
        
        if (title.includes(filter) || author.includes(filter) || desc.includes(filter)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (countBadge) {
        countBadge.textContent = visibleCount;
    }

    if (noResults) {
        noResults.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
    }
}
</script>

</body>
</html>

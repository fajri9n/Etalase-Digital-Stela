<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

// Fetch data project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    die("Data karya tidak ditemukan.");
}

// Pengecekan Otorisasi: Owner, Admin, atau Super Admin
$isOwner = ($_SESSION['user_id'] == $project['user_id']);
$isAdmin = in_array($_SESSION['role'], ['admin', 'super_admin']);

if (!$isOwner && !$isAdmin) {
    die("Akses Ditolak: Anda tidak memiliki izin untuk mengedit karya ini.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');
    $gambar_nama  = $project['gambar'];

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua bidang teks wajib diisi!";
    } else {
        // Jika user mengunggah gambar baru
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $file_info = pathinfo($_FILES['gambar']['name']);
            $ext = strtolower($file_info['extension'] ?? '');

            if (!in_array($ext, $allowed_exts)) {
                $error = "Format gambar tidak didukung! Gunakan JPG, PNG, WEBP, atau GIF.";
            } elseif ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
                $error = "Ukuran gambar terlalu besar! Maksimal 5MB.";
            } else {
                $new_name = uniqid('project_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $new_name)) {
                    // Hapus gambar lama jika ada
                    if (!empty($project['gambar']) && file_exists('uploads/' . $project['gambar'])) {
                        unlink('uploads/' . $project['gambar']);
                    }
                    $gambar_nama = $new_name;
                } else {
                    $error = "Gagal mengunggah file gambar baru.";
                }
            }
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE projects SET nama_project = :nama, deskripsi = :desk, gambar = :img, link_project = :link WHERE id = :id");
                $stmt->execute([
                    ':nama' => $nama_project,
                    ':desk' => $deskripsi,
                    ':img'  => $gambar_nama,
                    ':link' => $link_project,
                    ':id'   => $id
                ]);
                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                $error = "Gagal memperbarui karya: " . $e->getMessage();
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
    <title>Edit Karya - Etalase Digital Stella</title>
    <!-- Hubungkan CSS Base & CSS Form -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>

<nav class="navbar">
    <h2>Etalase Digital Stella</h2>
    <div class="navbar-user">
        <a href="index.php" class="btn btn-secondary">&larr; Kembali ke Etalase</a>
        <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="form-container">
    <h2>Edit Karya Digital</h2>

    <?php if (!empty($error)): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; font-size: 14px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_project">Nama Karya</label>
            <input type="text" id="nama_project" name="nama_project" value="<?= htmlspecialchars($project['nama_project']) ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" required><?= htmlspecialchars($project['deskripsi']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="link_project">Link Project</label>
            <input type="url" id="link_project" name="link_project" value="<?= htmlspecialchars($project['link_project']) ?>" required>
        </div>

        <div class="form-group">
            <label>Gambar Saat Ini</label>
            <div style="margin-bottom: 10px;">
                <img src="uploads/<?= htmlspecialchars($project['gambar']) ?>" 
                     alt="Gambar Saat Ini" 
                     style="max-width: 200px; max-height: 140px; border-radius: 5px; border: 1px solid #ced4da; object-fit: cover;"
                     onerror="this.onerror=null; this.src='assets/default_project.svg';">
            </div>
            
            <label for="gambar">Ganti Gambar (Opsional, Maks. 5MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
            
            <div id="previewBox" class="preview-box">
                <p style="font-size: 13px; color: #6c757d; margin-bottom: 6px;">Pratinjau Gambar Baru:</p>
                <img id="imagePreview" src="#" alt="Pratinjau Gambar Baru">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 15px;">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary" style="padding: 10px 20px; font-size: 15px;">Batal</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const previewBox = document.getElementById('previewBox');
    const imagePreview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewBox.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewBox.style.display = 'none';
    }
}
</script>

</body>
</html>

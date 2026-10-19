<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $link_project = trim($_POST['link_project'] ?? '');

    if (empty($nama_project) || empty($deskripsi) || empty($link_project)) {
        $error = "Semua bidang teks wajib diisi!";
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = "Gambar karya wajib diunggah!";
    } else {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $file_info = pathinfo($_FILES['gambar']['name']);
        $ext = strtolower($file_info['extension'] ?? '');

        if (!in_array($ext, $allowed_exts)) {
            $error = "Format gambar tidak didukung! Gunakan JPG, PNG, WEBP, atau GIF.";
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
                    $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project) 
                                           VALUES (:user_id, :nama, :desk, :img, :link)");
                    $stmt->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':nama'    => $nama_project,
                        ':desk'    => $deskripsi,
                        ':img'     => $new_name,
                        ':link'    => $link_project
                    ]);
                    header('Location: index.php');
                    exit();
                } catch (PDOException $e) {
                    $error = "Gagal menyimpan karya: " . $e->getMessage();
                }
            } else {
                $error = "Gagal mengunggah file gambar ke server. Pastikan folder uploads dapat ditulis.";
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
    <title>Tambah Karya - Etalase Digital Stella</title>
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
    <h2>Unggah Karya Digital Baru</h2>

    <?php if (!empty($error)): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; font-size: 14px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_project">Nama Karya / Aplikasi</label>
            <input type="text" id="nama_project" name="nama_project" placeholder="Contoh: Aplikasi Smart Garden IoT" value="<?= htmlspecialchars($_POST['nama_project'] ?? '') ?>" required autofocus>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi Karya</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan fitur, latar belakang, dan keunggulan karyamu..." required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="link_project">Link Project (GitHub / Demo Web / Video)</label>
            <input type="url" id="link_project" name="link_project" placeholder="https://..." value="<?= htmlspecialchars($_POST['link_project'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="gambar">Foto / Tangkapan Layar Karya (Maks. 5MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/*" required onchange="previewImage(this)">
            
            <div id="previewBox" class="preview-box">
                <img id="imagePreview" src="#" alt="Pratinjau Gambar">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 15px;">Simpan & Publikasikan</button>
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

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Super Admin</title>
    <!-- Hubungkan CSS Base, Form, dan Admin -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

<nav class="navbar">
    <h2>Manajemen User & Hak Akses</h2>
    <div class="navbar-user">
        <a href="../index.php" class="btn btn-secondary">&larr; Kembali ke Etalase</a>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
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

</body>
</html>

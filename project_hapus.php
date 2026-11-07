<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();

    if ($project) {
        // Pengecekan Otorisasi: Siswa hanya dapat menghapus miliknya sendiri, admin & super admin dapat menghapus karya siapa saja
        $isOwner = ($_SESSION['user_id'] == $project['user_id']);
        $isAdmin = in_array($_SESSION['role'], ['admin', 'super_admin']);

        if (!$isOwner && !$isAdmin) {
            die("Akses Ditolak: Anda tidak memiliki izin untuk menghapus karya ini.");
        }

        // Hapus file dari folder uploads/ jika file ada
        if (!empty($project['gambar']) && file_exists('uploads/' . $project['gambar'])) {
            unlink('uploads/' . $project['gambar']);
        }

        // Hapus data like yang berelasi dengan karya ini
        $delLikes = $pdo->prepare("DELETE FROM project_likes WHERE project_id = :id");
        $delLikes->execute([':id' => $id]);

        // Hapus baris data dari database
        $del = $pdo->prepare("DELETE FROM projects WHERE id = :id");
        $del->execute([':id' => $id]);
    }
}

header('Location: index.php');
exit();

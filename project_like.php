<?php
session_start();
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

// Cek apakah request menginginkan response JSON
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu untuk menyukai karya.'
        ]);
        exit();
    } else {
        header('Location: auth/login.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

$user_id    = (int) $_SESSION['user_id'];
$project_id = (int) ($_POST['project_id'] ?? 0);

if ($project_id <= 0) {
    if ($is_ajax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID karya tidak valid.']);
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

// Cek apakah karya ada
$stmtCheck = $pdo->prepare("SELECT id FROM projects WHERE id = :id");
$stmtCheck->execute([':id' => $project_id]);
if (!$stmtCheck->fetch()) {
    if ($is_ajax) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Karya tidak ditemukan.']);
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

try {
    // Cek apakah user sudah menyukai karya ini sebelumnya
    $stmtLike = $pdo->prepare("SELECT id FROM project_likes WHERE project_id = :project_id AND user_id = :user_id");
    $stmtLike->execute([':project_id' => $project_id, ':user_id' => $user_id]);
    $existing = $stmtLike->fetch();

    if ($existing) {
        // Jika sudah like -> unlike
        $stmtDel = $pdo->prepare("DELETE FROM project_likes WHERE project_id = :project_id AND user_id = :user_id");
        $stmtDel->execute([':project_id' => $project_id, ':user_id' => $user_id]);
        $is_liked = false;
        $action   = 'unliked';
    } else {
        // Jika belum like -> tambahkan like
        $stmtIns = $pdo->prepare("INSERT INTO project_likes (project_id, user_id) VALUES (:project_id, :user_id)");
        $stmtIns->execute([':project_id' => $project_id, ':user_id' => $user_id]);
        $is_liked = true;
        $action   = 'liked';
    }

    // Ambil total like terkini
    $stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM project_likes WHERE project_id = :project_id");
    $stmtCount->execute([':project_id' => $project_id]);
    $countRow    = $stmtCount->fetch();
    $total_likes = (int) ($countRow['total'] ?? 0);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'     => true,
            'action'      => $action,
            'is_liked'    => $is_liked,
            'total_likes' => $total_likes,
            'project_id'  => $project_id
        ]);
        exit();
    } else {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }
} catch (PDOException $e) {
    if ($is_ajax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem saat memproses like: ' . $e->getMessage()
        ]);
        exit();
    } else {
        header('Location: index.php?error=' . urlencode('Gagal memproses like'));
        exit();
    }
}

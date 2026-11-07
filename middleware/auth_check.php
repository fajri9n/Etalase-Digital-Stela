<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        $scriptPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
        $inSubfolder = (strpos($scriptPath, '/admin/') !== false);
        $redirectUrl = $inSubfolder ? '../auth/login.php' : 'auth/login.php';
        header("Location: $redirectUrl");
        exit();
    }
}

function check_role($roles = []) {
    check_login();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        die("Akses Ditolak: Anda tidak memiliki hak akses ke halaman ini.");
    }
}

function get_logged_user($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT id, nama, email, role, foto FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['foto'] = $user['foto'];
    }
    return $user;
}

/**
 * Daftar Kategori Karya Inovasi Digital Stella
 */
function get_project_categories() {
    return [
        'Web Design' => [
            'icon' => '🎨',
            'desc' => 'Desain website, landing page & mockup web interaktif',
            'class' => 'cat-badge-web-design'
        ],
        'Web Development' => [
            'icon' => '💻',
            'desc' => 'Aplikasi web fullstack, backend PHP & database',
            'class' => 'cat-badge-web-dev'
        ],
        'UI/UX Design' => [
            'icon' => '✨',
            'desc' => 'Desain antarmuka Figma, wireframe & prototipe UX',
            'class' => 'cat-badge-uiux'
        ],
        'Desain Grafis' => [
            'icon' => '🖌️',
            'desc' => 'Poster digital, banner, branding & ilustrasi grafis',
            'class' => 'cat-badge-graphic'
        ],
        'Mobile App' => [
            'icon' => '📱',
            'desc' => 'Aplikasi mobile Android, iOS, atau Flutter',
            'class' => 'cat-badge-mobile'
        ],
        'Game Development' => [
            'icon' => '🎮',
            'desc' => 'Game 2D/3D interaktif (Unity, Godot, Construct)',
            'class' => 'cat-badge-game'
        ],
        'IoT & Robotika' => [
            'icon' => '🤖',
            'desc' => 'Internet of Things, Arduino, ESP32 & sistem otomatisasi',
            'class' => 'cat-badge-iot'
        ],
        'Animasi & 3D' => [
            'icon' => '🎬',
            'desc' => 'Animasi digital, motion graphic & aset 3D Blender',
            'class' => 'cat-badge-animation'
        ],
        'Lainnya' => [
            'icon' => '💡',
            'desc' => 'Proyek inovasi teknologi & karya kreatif lainnya',
            'class' => 'cat-badge-other'
        ]
    ];
}

/**
 * Ambil metadata kategori spesifik dengan fallback default
 */
function get_category_info($kategori) {
    $categories = get_project_categories();
    return $categories[$kategori] ?? [
        'icon' => '💡',
        'desc' => 'Karya Digital',
        'class' => 'cat-badge-other'
    ];
}


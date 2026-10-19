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

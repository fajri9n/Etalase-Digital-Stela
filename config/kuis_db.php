<?php
/**
 * ==========================================================
 * ETALASE DIGITAL STELLA - KONFIGURASI DATABASE
 * Multi-Environment: Localhost (XAMPP/LAMPP) & Remote Hosting
 * ==========================================================
 */

$is_remote = (
    (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'xo.je') !== false || strpos($_SERVER['HTTP_HOST'], 'epizy') !== false)) ||
    (isset($_SERVER['SERVER_NAME']) && (strpos($_SERVER['SERVER_NAME'], 'xo.je') !== false || strpos($_SERVER['SERVER_NAME'], 'epizy') !== false))
);

if ($is_remote) {
    // Kredensial Hosting InfinityFree (epizy.com / etalase-digital-stela.xo.je)
    $host = 'sql112.epizy.com';
    $db   = 'if0_43361726_db_etalase_stella';
    $user = 'if0_43361726';
    $pass = 'EZgIgF2gShM6CV';
} else {
    // Kredensial Localhost (XAMPP / LAMPP / Dev)
    $host = 'localhost';
    $db   = 'db_etalase_stella';
    $user = 'root';
    $pass = '';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);

    // Auto-migration aman untuk memastikan kolom dan tabel penting selalu siap tanpa error
    try {
        // 1. Pastikan kolom 'foto' ada pada tabel users
        $colFoto = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'foto'");
        if ($colFoto && $colFoto->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `users` ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `role`");
        }

        // 2. Pastikan kolom 'kategori' ada pada tabel projects
        $colKat = $pdo->query("SHOW COLUMNS FROM `projects` LIKE 'kategori'");
        if ($colKat && $colKat->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `projects` ADD COLUMN `kategori` VARCHAR(50) NOT NULL DEFAULT 'Web Design' AFTER `link_project`");
        }

        // 3. Pastikan tabel 'project_likes' ada
        $pdo->exec("CREATE TABLE IF NOT EXISTS `project_likes` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `project_id` INT(10) UNSIGNED NOT NULL,
            `user_id` INT(10) UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_project_user_like` (`project_id`, `user_id`),
            KEY `fk_likes_project` (`project_id`),
            KEY `fk_likes_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $t) {
        // Abaikan jika izin database hosting membatasi eksekusi DDL
    }
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>

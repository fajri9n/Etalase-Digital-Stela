<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

check_login();

if ($_SESSION['role'] === 'super_admin') {
    header('Location: users.php');
} else {
    header('Location: ../index.php');
}
exit();

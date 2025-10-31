<?php
session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true,
]);

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'siakademikadb';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Helper fungsi login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}

function redirect_by_role($role) {
    switch ($role) {
        case 'admin':
            header("Location: Admin/index.php");
            break;
        case 'dosen':
            header("Location: Dosen/index.php");
            break;
        case 'mahasiswa':
            header("Location: Mahasiswa/index.php");
            break;
        default:
            header("Location: index.php");
            break;
    }
    exit;
}
?>

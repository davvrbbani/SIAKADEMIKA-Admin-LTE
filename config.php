<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, isset($_SERVER['HTTPS']),
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

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
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
// Fungsi untuk format waktu (FIXED VERSION)
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // HITUNG MINGGU TANPA MENGGUNAKAN DYNAMIC PROPERTY
    $weeks = floor($diff->d / 7);
    $days_remaining = $diff->d - ($weeks * 7);

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan', 
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    
    // Buat array hasil dengan nilai yang sudah dihitung
    $result = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days_remaining,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];
    
    foreach ($string as $k => &$v) {
        if ($result[$k] > 0) {
            $v = $result[$k] . ' ' . $v . ($result[$k] > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
function log_activity($pdo, $user_id, $action_message) {
    try {
        $sql = "INSERT INTO activity_logs (user_id, action_message) VALUES (:user_id, :action_message)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':action_message', $action_message, PDO::PARAM_STR);
        
        $stmt->execute();

    } catch (PDOException $e) {
        // Jika gagal mencatat log, jangan hentikan script utama
        // Cukup catat errornya di server log (opsional)
        error_log("Gagal mencatat aktivitas: " . $e->getMessage());
    }
}
?>

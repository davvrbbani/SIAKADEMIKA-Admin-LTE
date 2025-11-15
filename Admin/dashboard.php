<?php
// LANGKAH 1 & 2: INI ADALAH BLOK PHP ASLI ANDA (MESIN)
// JANGAN DIHAPUS, KARENA INI YANG MENGAMBIL DATA
require_once '../config.php';
require_login();

$username = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');

// Inisialisasi variabel count
$jumlah_mahasiswa = 0;
$jumlah_dosen = 0;
$jumlah_kelas = 0;
$jumlah_kritik = 0;

try {
    // LANGKAH 3: Eksekusi Query yang Efisien
    // Mengambil total mahasiswa dan dosen
    $query_count = "SELECT role, COUNT(id) as total 
                    FROM users 
                    WHERE role IN ('mahasiswa', 'dosen') 
                    GROUP BY role";
    $stmt = $pdo->query($query_count);
    $results = $stmt->fetchAll();

    foreach ($results as $row) {
        if ($row['role'] == 'mahasiswa') {
            $jumlah_mahasiswa = $row['total'];
        } elseif ($row['role'] == 'dosen') {
            $jumlah_dosen = $row['total'];
        }
    }
    
    // Query untuk jumlah kelas
    $query_kelas = "SELECT COUNT(id) as total_kelas FROM kelas";
    $stmt_kelas = $pdo->query($query_kelas);
    $jumlah_kelas = $stmt_kelas->fetchColumn();

    // Query untuk aktivitas terbaru (contoh - sesuaikan dengan tabel log Anda)
    // Query ini sebenarnya tidak terpakai karena Anda query lagi di bawah
    // $query_log = "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5";
    // $stmt_log = $pdo->query($query_log);
    // $activity_logs = $stmt_log->fetchAll();

    // Query untuk kritik & saran hari ini
    $query_kritik = "SELECT COUNT(id) as total_kritik FROM kritik_saran WHERE DATE(created_at) = CURDATE()";
    $stmt_kritik = $pdo->query($query_kritik);
    $jumlah_kritik = $stmt_kritik->fetchColumn();

} catch (PDOException $e) {
    // Untuk development, tampilkan error. Untuk production, log error saja
    error_log("Database error: " . $e->getMessage());
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0 text-gray-800">Dashboard Admin</h3>
                <p class="mb-0 text-muted">Hai, Selamat Datang <b><?php echo $username; ?>!</b></p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>

    <div class="app-content">
        <div class="container-fluid">
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2" style="background: linear-gradient(135deg, #f94632ff 0%, #75fa75ff 100%);">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Total Dosen</div>
                                <div class="h2 mb-0 font-weight-bold text-white"><?php echo $jumlah_dosen; ?></div>
                            </div>
                            <div class="col-lg-auto">
                                <i class="bi bi-mortarboard fs-2 text-white-300"></i>
                            </div>
                        </div>
                    </div>
                    <a href="?p=dosen" class="card-footer text-white text-decoration-none d-block text-center py-2" style="background: rgba(0,0,0,0.1);">
                        Lihat Detail <i class="bi bi-arrow-right-circle ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2" style="background: linear-gradient(135deg, #71fc4fff 0%, #4dc6f9ff 100%);">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Total Mahasiswa</div>
                                <div class="h2 mb-0 font-weight-bold text-white"><?php echo $jumlah_mahasiswa; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-2 text-white-300"></i>
                            </div>
                        </div>
                    </div>
                    <a href="?p=mahasiswa" class="card-footer text-white text-decoration-none d-block text-center py-2" style="background: rgba(0,0,0,0.1);">
                        Lihat Detail <i class="bi bi-arrow-right-circle ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                    Total Kelas</div>
                                <div class="h2 mb-0 font-weight-bold text-white"><?php echo $jumlah_kelas; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-building fs-2 text-white-300"></i>
                            </div>
                        </div>
                    </div>
                    <a href="?p=kelas" class="card-footer text-white text-decoration-none d-block text-center py-2" style="background: rgba(0,0,0,0.1);">
                        Lihat Detail <i class="bi bi-arrow-right-circle ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-rocket-takeoff-fill me-2"></i>Jalan Pintas</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="?p=add-mahasiswa" class="btn btn-primary btn-block p-3 d-flex align-items-center justify-content-center shadow">
                                    <i class="bi bi-person-plus-fill fs-4 me-3"></i>
                                    <span class="fw-bold">Tambah Mahasiswa</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="?p=add-dosen" class="btn btn-success btn-block p-3 d-flex align-items-center justify-content-center shadow">
                                    <i class="bi bi-person-video fs-4 me-3"></i>
                                    <span class="fw-bold">Tambah Dosen</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="?p=jadwal-kuliah" class="btn btn-info btn-block p-3 d-flex align-items-center justify-content-center shadow">
                                    <i class="bi bi-calendar-week fs-4 me-3"></i>
                                    <span class="fw-bold">Atur Jadwal Kuliah</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="?p=add-prestasi" class="btn btn-warning btn-block p-3 d-flex align-items-center justify-content-center shadow">
                                    <i class="bi bi-trophy fs-4 me-3"></i>
                                    <span class="fw-bold">Input Prestasi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru (Log)</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php
                            // Query untuk mengambil activity log terbaru
                            try {
                                $log_query = "SELECT al.*, u.username 
                                              FROM activity_logs al 
                                              LEFT JOIN users u ON al.user_id = u.id 
                                              ORDER BY al.created_at DESC 
                                              LIMIT 5";
                                $log_stmt = $pdo->query($log_query);
                                $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (!empty($activity_logs)) {
                                    foreach ($activity_logs as $log) {
                                        // PASTIKAN FUNGSI INI ADA DAN TIDAK DI-KOMENTAR DI BAWAH
                                        $time_ago = time_elapsed_string($log['created_at']); 
                                        $log_username = $log['username'] ?: 'System';
                                        
                                        // Deteksi jenis aksi untuk icon
                                        $action_lower = strtolower($log['action_message']);
                                        $icon_type = 'info-circle';
                                        $color_type = 'secondary';
                                        
                                        if (strpos($action_lower, 'tambah') !== false || strpos($action_lower, 'menambahkan') !== false) {
                                            $icon_type = 'bi bi-database-add';
                                            $color_type = 'success';
                                        } elseif (strpos($action_lower, 'edit') !== false || strpos($action_lower, 'mengedit') !== false) {
                                            $icon_type = 'pencil-square'; // Ikon 'edit' yang benar
                                            $color_type = 'warning';
                                        } elseif (strpos($action_lower, 'hapus') !== false || strpos($action_lower, 'menghapus') !== false) {
                                            $icon_type = 'trash';
                                            $color_type = 'danger';
                                        }
                                        
                                        echo '<li class="list-group-item d-flex align-items-center py-3">
                                                <i class="bi bi-'.$icon_type.' fs-4 text-'.$color_type.' me-3"></i>
                                                <div>
                                                    <strong>' . htmlspecialchars($log_username) . '</strong> ' . htmlspecialchars($log['action_message']) . '
                                                    <small class="d-block text-muted">' . $time_ago . '</small>
                                                </div>
                                            </li>';
                                    }
                                } else {
                                    // Fallback jika tidak ada log
                                    echo '<li class="list-group-item d-flex align-items-center py-3">
                                            <i class="bi bi-person-plus-fill fs-4 text-success me-3"></i>
                                            <div>
                                                <strong>Admin ' . htmlspecialchars($username) . '</strong> baru saja menambahkan mahasiswa <strong>Budi Santoso</strong>.
                                                <small class="d-block text-muted">2 menit yang lalu</small>
                                            </div>
                                        </li>';
                                }
                            } catch (PDOException $e) {
                                echo '<li class="list-group-item text-danger">Error loading activity log: ' . htmlspecialchars($e->getMessage()) . '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-white text-center py-3">
                        <a href="?p=activity" class="text-decoration-none text-primary fw-bold">
                            Lihat Semua Aktivitas <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php
// Helper functions untuk activity log
// PASTIKAN FUNGSI time_elapsed_string() DI BAWAH INI TIDAK DI-KOMENTAR (HAPUS TANDA //)
// JIKA ANDA MEMBUTUHKANNYA UNTUK LOG AKTIVITAS.

/*
function getActivityIcon($type) {
    $icons = [
        'add_mahasiswa' => 'user-plus',
        'add_dosen' => 'chalkboard-teacher',
        'update_dosen' => 'user-check',
        'publish_jadwal' => 'calendar-check',
        'kritik_saran' => 'envelope'
    ];
    return $icons[$type] ?? 'info-circle';
}

function getActivityColor($type) {
    $colors = [
        'add_mahasiswa' => 'success',
        'add_dosen' => 'primary',
        'update_dosen' => 'info',
        'publish_jadwal' => 'warning',
        'kritik_saran' => 'danger'
    ];
    return $colors[$type] ?? 'secondary';
}
*/

// HAPUS TANDA // DARI BLOK DI BAWAH INI JIKA ERROR "undefined function time_elapsed_string"
/*
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
*/
?>
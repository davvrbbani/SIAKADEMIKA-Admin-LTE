<?php
// ==============================================================
// 1. KONFIGURASI & QUERY DATA
// ==============================================================
require_once '../config.php';
require_login();

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['user_name']);

try {
    // A. CARD STATISTIK UTAMA
    // 1. Total Dosen
    $qDosen = $pdo->query("SELECT COUNT(id) FROM dosen");
    $total_dosen = $qDosen->fetchColumn();

    // 2. Total Mahasiswa
    $qMhs = $pdo->query("SELECT COUNT(id) FROM mahasiswa");
    $total_mhs = $qMhs->fetchColumn();

    // 3. Total Mata Kuliah
    $qMatkul = $pdo->query("SELECT COUNT(id) FROM mata_kuliah");
    $total_matkul = $qMatkul->fetchColumn();

    // 4. Total Kelas
    $qKelas = $pdo->query("SELECT COUNT(id) FROM kelas");
    $total_kelas = $qKelas->fetchColumn();


    // B. DATA UNTUK CHART: KOMPOSISI USER
    $qRoles = $pdo->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
    $roleData = $qRoles->fetchAll(PDO::FETCH_KEY_PAIR); // Hasil: ['admin'=>1, 'dosen'=>5, ...]
    
    // Siapkan data JSON untuk JS
    $chartRoleLabels = array_keys($roleData);
    $chartRoleValues = array_values($roleData);


    // C. DATA UNTUK CHART: KEPADATAN JADWAL PER HARI
    // Mengurutkan hari dari Senin - Minggu
    $qJadwal = $pdo->query("
        SELECT hari, COUNT(*) as total 
        FROM jadwal_kuliah 
        GROUP BY hari 
        ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
    ");
    $jadwalData = $qJadwal->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $chartJadwalLabels = array_keys($jadwalData);
    $chartJadwalValues = array_values($jadwalData);


    // D. DATA UNTUK CHART: PRESTASI MAHASISWA
    $qPrestasi = $pdo->query("SELECT tingkat, COUNT(*) as total FROM prestasi_mahasiswa GROUP BY tingkat");
    $prestasiData = $qPrestasi->fetchAll(PDO::FETCH_KEY_PAIR);

    $chartPrestasiLabels = array_keys($prestasiData);
    $chartPrestasiValues = array_values($prestasiData);


    // E. ACTIVITY LOG TERBARU (Limit 5)
    $log_query = "SELECT al.*, u.username 
                  FROM activity_logs al 
                  LEFT JOIN users u ON al.user_id = u.id 
                  ORDER BY al.created_at DESC LIMIT 6";
    $log_stmt = $pdo->query($log_query);
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold text-dark">Dashboard Admin</h3>
                    <p class="text-muted small">Selamat datang kembali, <strong><?= $username ?></strong>!</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-primary shadow-sm">
                        <div class="inner">
                            <h3><?= $total_dosen ?></h3>
                            <p>Total Dosen</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="bi bi-person-video3"></i>
                        </div>
                        <a href="?p=dosen" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success shadow-sm">
                        <div class="inner">
                            <h3><?= $total_mhs ?></h3>
                            <p>Total Mahasiswa</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <a href="?p=mahasiswa" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning text-white shadow-sm">
                        <div class="inner">
                            <h3><?= $total_matkul ?></h3>
                            <p>Mata Kuliah</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <a href="?p=matakuliah" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger shadow-sm">
                        <div class="inner">
                            <h3><?= $total_kelas ?></h3>
                            <p>Total Kelas</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="bi bi-buildings-fill"></i>
                        </div>
                        <a href="?p=kelas" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                
                <div class="col-lg-8">
                    
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 bg-white pt-3">
                            <h5 class="card-title fw-bold"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Kepadatan Jadwal Kuliah</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartJadwal" height="100"></canvas>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm border-0">
                                <div class="card-header border-0 bg-white pt-3">
                                    <h5 class="card-title fw-bold"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Komposisi User</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartRoles" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm border-0">
                                <div class="card-header border-0 bg-white pt-3">
                                    <h5 class="card-title fw-bold"><i class="bi bi-trophy-fill me-2 text-warning"></i>Sebaran Prestasi</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartPrestasi" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-dark text-white border-0">
                            <h3 class="card-title"><i class="bi bi-activity me-2"></i> Log Aktivitas Terbaru</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if (empty($activity_logs)): ?>
                                    <div class="p-4 text-center text-muted">Belum ada aktivitas tercatat.</div>
                                <?php else: ?>
                                    <?php foreach ($activity_logs as $log): 
                                        $time_ago = time_elapsed_string($log['created_at']);
                                        $user = $log['username'] ?: 'System';
                                    ?>
                                    <div class="list-group-item px-3 py-3">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small class="fw-bold text-primary"><?= htmlspecialchars($user) ?></small>
                                            <small class="text-muted"><?= $time_ago ?></small>
                                        </div>
                                        <p class="mb-1 small text-dark"><?= htmlspecialchars($log['action_message']) ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-light">
                            <small>Hanya menampilkan 6 aktivitas terakhir</small>
                        </div>
                        <div class="card-footer text-center bg-light">
                            <a href="./?p=activity">lihat selengkapnya</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. CHART KEPADATAN JADWAL (BAR)
    const ctxJadwal = document.getElementById('chartJadwal').getContext('2d');
    new Chart(ctxJadwal, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartJadwalLabels) ?>,
            datasets: [{
                label: 'Jumlah Kelas',
                data: <?= json_encode($chartJadwalValues) ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // 2. CHART KOMPOSISI USER (DOUGHNUT)
    const ctxRoles = document.getElementById('chartRoles').getContext('2d');
    new Chart(ctxRoles, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_map('ucfirst', $chartRoleLabels)) ?>,
            datasets: [{
                data: <?= json_encode($chartRoleValues) ?>,
                backgroundColor: ['#dc3545', '#0d6efd', '#198754'], // Merah (Admin), Biru (Dosen), Hijau (Mhs)
                hoverOffset: 4
            }]
        },
        options: { responsive: true }
    });

    // 3. CHART PRESTASI (PIE)
    const ctxPrestasi = document.getElementById('chartPrestasi').getContext('2d');
    new Chart(ctxPrestasi, {
        type: 'pie',
        data: {
            labels: <?= json_encode($chartPrestasiLabels) ?>,
            datasets: [{
                data: <?= json_encode($chartPrestasiValues) ?>,
                backgroundColor: ['#6c757d', '#17a2b8', '#ffc107', '#fd7e14'],
            }]
        },
        options: { responsive: true }
    });
});
</script>
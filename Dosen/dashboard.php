<?php
// ==============================================================
// 1. KONFIGURASI & LOGIKA BACKEND
// ==============================================================
require_once "../config.php"; 

require_login(); 
if ($_SESSION['user_role'] !== 'dosen') {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit;
}

$user_id_login = $_SESSION['user_id'];

// Ambil Data Dosen
$stmt = $pdo->prepare("SELECT id, nama_lengkap, nidn FROM dosen WHERE user_id = ?");
$stmt->execute([$user_id_login]);
$dosen = $stmt->fetch();

if (!$dosen) die("Error: Data Dosen tidak ditemukan.");
$id_dosen = $dosen['id']; 

// --- HELPER: TRANSLATE HARI KE INDONESIA ---
function getHariIndo($dayInggris) {
    $arrHari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $arrHari[$dayInggris] ?? 'Minggu';
}
$hari_ini = getHariIndo(date('l'));

// --- HELPER: HITUNG SEMESTER (Sama seperti di jadwal.php) ---
function hitungSemester($angkatan) {
    $tahun_sekarang = date('Y');
    $bulan_sekarang = date('n');
    
    $selisih_tahun = $tahun_sekarang - $angkatan;
    
    // Jika bulan >= Agustus (8), masuk semester Ganjil (1, 3, 5...)
    if ($bulan_sekarang >= 8) {
        $semester = ($selisih_tahun * 2) + 1;
    } else {
        $semester = ($selisih_tahun * 2);
    }
    return ($semester > 0) ? $semester : 1;
}

// --- QUERY 1: STATISTIK CARD ---
// A. Total Kelas yg diajar
$q1 = $pdo->prepare("SELECT COUNT(DISTINCT kelas_id) FROM jadwal_kuliah WHERE dosen_id = ?");
$q1->execute([$id_dosen]);
$total_kelas = $q1->fetchColumn();

// B. Total Materi yg sudah diupload
$q2 = $pdo->prepare("
    SELECT COUNT(m.id) FROM materi_kuliah m 
    JOIN jadwal_kuliah jk ON m.jadwal_kuliah_id = jk.id 
    WHERE jk.dosen_id = ?
");
$q2->execute([$id_dosen]);
$total_materi = $q2->fetchColumn();

// C. Total SKS yg diampu
$q3 = $pdo->prepare("
    SELECT SUM(mk.sks) FROM jadwal_kuliah jk 
    JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id 
    WHERE jk.dosen_id = ?
");
$q3->execute([$id_dosen]);
$total_sks = $q3->fetchColumn() ?: 0;

// D. Total Mahasiswa (Estimasi dari kelas yg diajar)
$q4 = $pdo->prepare("
    SELECT COUNT(mhs.id) FROM mahasiswa mhs 
    JOIN jadwal_kuliah jk ON mhs.kelas_id = jk.kelas_id 
    WHERE jk.dosen_id = ?
");
$q4->execute([$id_dosen]);
$total_mhs = $q4->fetchColumn();


// --- QUERY 2: JADWAL HARI INI (REALTIME) ---
// UPDATE: Tambah k.angkatan di select agar bisa hitung semester
$qJadwal = $pdo->prepare("
    SELECT jk.*, mk.nama_mk, mk.kode_mk, mk.sks, k.kelas, k.angkatan 
    FROM jadwal_kuliah jk
    JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
    JOIN kelas k ON jk.kelas_id = k.id
    WHERE jk.dosen_id = ? AND jk.hari = ?
    ORDER BY jk.jam_mulai ASC
");
$qJadwal->execute([$id_dosen, $hari_ini]);
$jadwal_hari_ini = $qJadwal->fetchAll(PDO::FETCH_ASSOC);


// --- QUERY 3: DATA CHART (Jumlah Materi per Mata Kuliah) ---
$qChart = $pdo->prepare("
    SELECT mk.nama_mk, COUNT(m.id) as jumlah_materi
    FROM jadwal_kuliah jk
    JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
    LEFT JOIN materi_kuliah m ON jk.id = m.jadwal_kuliah_id
    WHERE jk.dosen_id = ?
    GROUP BY mk.id, mk.nama_mk
");
$qChart->execute([$id_dosen]);
$chartData = $qChart->fetchAll(PDO::FETCH_ASSOC);

// Siapkan JSON untuk Chart JS
$labels = [];
$dataMateri = [];
foreach($chartData as $d) {
    $labels[] = $d['nama_mk'];
    $dataMateri[] = $d['jumlah_materi'];
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Nunito', sans-serif; background-color: #f3f4f6; }
    
    /* Welcome Card */
    .welcome-card {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 20px rgba(78, 115, 223, 0.2);
        position: relative;
        overflow: hidden;
    }
    .welcome-card::after {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    /* Stat Cards */
    .stat-card {
        border: none;
        border-radius: 12px;
        background: #fff;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        transition: transform 0.2s;
        height: 100%;
        border-left: 4px solid transparent;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon {
        width: 45px; height: 45px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 15px;
    }
    .st-blue { border-left-color: #4e73df; } .ic-blue { background: #e8f0fe; color: #4e73df; }
    .st-green { border-left-color: #1cc88a; } .ic-green { background: #e6fffa; color: #1cc88a; }
    .st-info { border-left-color: #36b9cc; } .ic-info { background: #e0f7fa; color: #36b9cc; }
    .st-orange { border-left-color: #f6c23e; } .ic-orange { background: #fff8e1; color: #f6c23e; }

    .stat-value { font-size: 1.5rem; font-weight: 800; color: #2d3748; }
    .stat-label { color: #718096; font-size: 0.9rem; font-weight: 600; }

    /* Jadwal List */
    .timeline-item {
        padding: 15px 0;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
    }
    .timeline-item:last-child { border-bottom: none; }
    .time-col { width: 100px; flex-shrink: 0; font-weight: 700; color: #4e73df; }
    .mk-col { flex-grow: 1; }
    .mk-name { font-weight: 700; color: #2d3748; font-size: 1rem; }
    .mk-info { font-size: 0.85rem; color: #718096; margin-top: 3px; }
    .room-badge {
        background: #edf2f7; color: #4a5568; 
        padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;
    }

    /* Chart Container */
    .chart-container {
        position: relative; 
        height: 300px; 
        width: 100%;
    }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0 fw-bold">Dashboard</h3></div>
            <div class="col-sm-6 text-end">
                <span class="text-muted small"><?= getHariIndo(date('l')) ?>, <?= date('d F Y') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="welcome-card d-flex align-items-center justify-content-between">
            <div>
                <h2 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($dosen['nama_lengkap']) ?>!</h2>
                <p class="mb-0 opacity-75">Selamat beraktivitas. Jangan lupa cek jadwal mengajar Anda hari ini.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-chalkboard-teacher fa-4x opacity-50"></i>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card st-blue">
                    <div class="stat-icon ic-blue"><i class="fas fa-chalkboard"></i></div>
                    <div class="stat-value"><?= $total_kelas ?></div>
                    <div class="stat-label">Total Kelas Ajar</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card st-green">
                    <div class="stat-icon ic-green"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-value"><?= $total_materi ?></div>
                    <div class="stat-label">Materi Diupload</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card st-info">
                    <div class="stat-icon ic-info"><i class="fas fa-book"></i></div>
                    <div class="stat-value"><?= $total_sks ?></div>
                    <div class="stat-label">Total SKS</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card st-orange">
                    <div class="stat-icon ic-orange"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?= $total_mhs ?></div>
                    <div class="stat-label">Total Mahasiswa</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">Jadwal Hari Ini (<?= $hari_ini ?>)</h5>
                        <a href="?p=jadwal" class="btn btn-sm btn-light text-primary fw-bold">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if(empty($jadwal_hari_ini)): ?>
                            <div class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                                <h6 class="text-muted fw-bold">Tidak ada jadwal mengajar hari ini.</h6>
                                <p class="text-muted small">Nikmati waktu luang Anda atau persiapkan materi untuk besok!</p>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <?php foreach($jadwal_hari_ini as $j): 
                                    // Hitung Semester
                                    $smt = hitungSemester($j['angkatan']);
                                ?>
                                    <div class="timeline-item">
                                        <div class="time-col">
                                            <?= date('H:i', strtotime($j['jam_mulai'])) ?><br>
                                            <span class="text-muted fw-normal small"><?= date('H:i', strtotime($j['jam_selesai'])) ?></span>
                                        </div>
                                        <div class="mk-col">
                                            <div class="mk-name"><?= htmlspecialchars($j['nama_mk']) ?></div>
                                            <div class="mk-info">
                                                <i class="fas fa-layer-group me-1"></i> 
                                                Semester <?= $smt ?> - Kelas <?= htmlspecialchars($j['kelas']) ?>
                                                <span class="mx-1">•</span> 
                                                <?= $j['sks'] ?> SKS
                                            </div>
                                            <div class="mt-2">
                                                <span class="room-badge"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($j['ruangan']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="ms-2">
                                            <?php 
                                                $now = date('H:i:s');
                                                $start = $j['jam_mulai'];
                                                $end = $j['jam_selesai'];

                                                if ($now >= $start && $now <= $end) {
                                                    echo '<span class="badge bg-success animate__animated animate__pulse animate__infinite"><i class="fas fa-video me-1"></i> Berlangsung</span>';
                                                } elseif ($now > $end) {
                                                    echo '<span class="badge bg-secondary text-white border"><i class="fas fa-check me-1"></i> Selesai</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-start me-1"></i> Segera</span>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0 text-dark">Sebaran Materi Ajar</h5>
                        <small class="text-muted">Jumlah materi yang sudah diupload per mata kuliah</small>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="materiChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-dark text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <h5 class="fw-bold mb-1">Upload Materi Baru?</h5>
                            <p class="mb-3 opacity-75 small">Bagikan bahan ajar terbaru untuk mahasiswa Anda.</p>
                            <a href="?p=materi" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Upload Sekarang</a>
                        </div>
                        <i class="fas fa-cloud-upload-alt fa-4x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Inisialisasi Chart JS
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('materiChart').getContext('2d');
        
        // Data dari PHP
        const labels = <?= json_encode($labels) ?>;
        const dataMateri = <?= json_encode($dataMateri) ?>;

        // Cek jika data kosong
        if (labels.length === 0) {
            document.querySelector('.chart-container').innerHTML = '<div class="text-center py-5 text-muted">Belum ada data materi.</div>';
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah File/Link Materi',
                    data: dataMateri,
                    backgroundColor: 'rgba(78, 115, 223, 0.7)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
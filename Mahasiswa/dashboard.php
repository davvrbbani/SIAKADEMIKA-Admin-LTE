<?php
// Query untuk mengambil data jadwal kuliah menggunakan PDO
$query = "SELECT j.*, mk.nama_mk, d.nama_lengkap, k.kelas 
          FROM jadwal_kuliah j
          JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
          JOIN dosen d ON j.dosen_id = d.id
          JOIN kelas k ON j.kelas_id = k.id
          ORDER BY 
            FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
            j.jam_mulai";

try {
    $stmt = $pdo->query($query);
    $jadwalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error mengambil data jadwal: " . $e->getMessage());
}

// Mendapatkan waktu saat ini dengan timezone Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
$sekarang = date('H:i:s'); // Format waktu sekarang sebagai string
$hariIni = date('l');

// Debug: Tampilkan waktu server saat ini (opsional, bisa dihapus setelah testing)
// echo "<!-- Debug: Waktu server: " . $sekarang . " -->";

// Mapping nama hari dalam bahasa Inggris ke Indonesia
$hariMapping = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

$hariIniIndonesia = $hariMapping[$hariIni];

// Ambil jadwal hari ini yang sedang berlangsung
$jadwalBerlangsung = [];
$jadwalHariIni = [];

foreach ($jadwalData as $jadwal) {
    if ($jadwal['hari'] === $hariIniIndonesia) {
        $jadwalHariIni[] = $jadwal;
        
        // Cek apakah jadwal sedang berlangsung dengan perbandingan string waktu
        if ($sekarang >= $jadwal['jam_mulai'] && $sekarang <= $jadwal['jam_selesai']) {
            $jadwalBerlangsung[] = $jadwal;
        }
    }
}
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold text-dark">Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <!-- Welcome Message -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="welcome-title">Selamat Datang, <?php echo htmlspecialchars($current_student['nama_lengkap']); ?>! 👋</h4>
                                <p class="welcome-text">Selamat datang di Dashboard Sistem Informasi Akademik. Kelola aktivitas akademik Anda dengan mudah.</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="time-display">
                                    <div id="waktu-sekarang" class="current-time"><?php echo date('H:i:s'); ?></div>
                                    <small class="text-muted">Hari Ini: <?php echo $hariIniIndonesia; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal Berlangsung -->
            <?php if (!empty($jadwalBerlangsung)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card live-class-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="live-indicator"></div>
                                <h5 class="card-title mb-0 ms-2">Sedang Berlangsung</h5>
                            </div>
                            <?php foreach ($jadwalBerlangsung as $jadwal): ?>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="class-name"><?php echo htmlspecialchars($jadwal['nama_mk']); ?></h6>
                                    <p class="class-info mb-1">
                                        <i class="bi bi-person me-2"></i><?php echo htmlspecialchars($jadwal['nama_lengkap']); ?>
                                        <i class="bi bi-geo-alt me-2 ms-3"></i><?php echo htmlspecialchars($jadwal['ruangan']); ?>
                                    </p>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('H:i', strtotime($jadwal['jam_mulai'])) . ' - ' . date('H:i', strtotime($jadwal['jam_selesai'])); ?>
                                    </span>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="time-remaining" data-end="<?php echo $jadwal['jam_selesai']; ?>">
                                        <small class="text-muted">Selesai dalam:</small>
                                        <div class="remaining-time">00:00:00</div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Shortcut Menu -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="section-title">Menu Utama</h5>
                </div>
                
                <!-- Jadwal Matkul -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="./?p=jadwal-kuliah" class="card-link">
                        <div class="card shortcut-card gradient-1">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-text"><strong>Jadwal Matkul</strong></h6>
                                        <p class="card-text">Lihat jadwal kuliah mingguan</p>
                                    </div>
                                    <div class="icon-wrapper">
                                        <i class="bi bi-calendar-week"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Materi Kuliah -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="./?p=materi-kuliah" class="card-link">
                        <div class="card shortcut-card gradient-2">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-text"><strong>Materi Kuliah</strong></h6>
                                        <p class="card-text">Download materi perkuliahan</p>
                                    </div>
                                    <div class="icon-wrapper">
                                        <i class="bi bi-book"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Kejuaraan -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="./?p=prestasi-mahasiswa" class="card-link">
                        <div class="card shortcut-card gradient-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-text"><strong>Kejuaraan</strong></h6>
                                        <p class="card-text">Kelola data prestasi Anda</p>
                                    </div>
                                    <div class="icon-wrapper">
                                        <i class="bi bi-trophy"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Kritik dan Saran -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <a href="./?p=kritik-saran" class="card-link">
                        <div class="card shortcut-card gradient-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-text"><strong>Kritik & Saran</strong></h6>
                                        <p class="card-text">Kirim masukan untuk kampus</p>
                                    </div>
                                    <div class="icon-wrapper">
                                        <i class="bi bi-chat-right-quote"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Jadwal Hari Ini -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-day me-2"></i>Jadwal Hari Ini (<?php echo $hariIniIndonesia; ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($jadwalHariIni)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mata Kuliah</th>
                                                <th>Dosen</th>
                                                <th>Waktu</th>
                                                <th>Ruangan</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($jadwalHariIni as $jadwal): 
                                                $isBerlangsung = ($sekarang >= $jadwal['jam_mulai'] && $sekarang <= $jadwal['jam_selesai']);
                                                $isSelesai = ($sekarang > $jadwal['jam_selesai']);
                                                $isAkanDatang = ($sekarang < $jadwal['jam_mulai']);
                                            ?>
                                            <tr class="<?php echo $isBerlangsung ? 'table-success' : ($isSelesai ? 'table-light' : ''); ?>">
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($jadwal['nama_mk']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($jadwal['kelas']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($jadwal['nama_lengkap']); ?></td>
                                                <td>
                                                    <?php 
                                                    echo date('H:i', strtotime($jadwal['jam_mulai'])) . 
                                                         ' - ' . 
                                                         date('H:i', strtotime($jadwal['jam_selesai'])); 
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                                <td>
                                                    <?php if ($isBerlangsung): ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-play-circle me-1"></i>Sedang Berlangsung
                                                        </span>
                                                    <?php elseif ($isSelesai): ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-clock me-1"></i>Akan Datang
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">Tidak ada jadwal kuliah hari ini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<!-- JavaScript -->
<script>
// Update waktu real-time
function updateWaktu() {
    const sekarang = new Date();
    
    const jam = sekarang.getHours().toString().padStart(2, '0');
    const menit = sekarang.getMinutes().toString().padStart(2, '0');
    const detik = sekarang.getSeconds().toString().padStart(2, '0');
    
    const waktuString = `${jam}:${menit}:${detik}`;
    
    document.getElementById('waktu-sekarang').textContent = waktuString;
    
    // Update status jadwal secara real-time
    updateStatusJadwal(jam, menit, detik);
}

// Update status jadwal berdasarkan waktu
function updateStatusJadwal(jam, menit, detik) {
    const waktuSekarang = `${jam}:${menit}:${detik}`;
    
    document.querySelectorAll('tbody tr').forEach(row => {
        const waktuCell = row.cells[2];
        const statusCell = row.cells[4];
        const waktuText = waktuCell.textContent.trim();
        
        // Extract start and end time from format "HH:MM - HH:MM"
        const [startTime, endTime] = waktuText.split(' - ');
        const startTimeFull = startTime + ':00';
        const endTimeFull = endTime + ':00';
        
        let newStatus = '';
        let newBadgeClass = '';
        let newRowClass = '';
        
        if (waktuSekarang >= startTimeFull && waktuSekarang <= endTimeFull) {
            newStatus = '<span class="badge bg-secondary"><i class="bi bi-play-circle me-1"></i>Sedang Berlangsung</span>';
            newBadgeClass = 'bg-secondary';
            newRowClass = 'table-light';
        } else if (waktuSekarang > endTimeFull) {
            newStatus = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
            newBadgeClass = 'bg-success';
            newRowClass = 'table-success';
        } else {
            newStatus = '<span class="badge bg-info"><i class="bi bi-clock me-1"></i>Akan Datang</span>';
            newBadgeClass = 'bg-info';
            newRowClass = '';
        }
        
        // Update status cell
        statusCell.innerHTML = newStatus;
        
        // Update row class
        row.className = newRowClass;
        
        // Update badge class for consistency
        const badge = statusCell.querySelector('.badge');
        if (badge) {
            badge.className = `badge ${newBadgeClass}`;
        }
    });
}

// Hitung waktu tersisa untuk jadwal berlangsung
function updateTimeRemaining() {
    const sekarang = new Date();
    const totalDetikSekarang = sekarang.getHours() * 3600 + sekarang.getMinutes() * 60 + sekarang.getSeconds();
    
    document.querySelectorAll('.time-remaining').forEach(element => {
        const endTime = element.getAttribute('data-end');
        const [hours, minutes, seconds] = endTime.split(':');
        const totalDetikAkhir = parseInt(hours) * 3600 + parseInt(minutes) * 60 + parseInt(seconds);
        
        if (totalDetikSekarang < totalDetikAkhir) {
            const sisaDetik = totalDetikAkhir - totalDetikSekarang;
            const jamLeft = Math.floor(sisaDetik / 3600);
            const menitLeft = Math.floor((sisaDetik % 3600) / 60);
            const detikLeft = sisaDetik % 60;
            
            element.querySelector('.remaining-time').textContent = 
                `${jamLeft.toString().padStart(2, '0')}:${menitLeft.toString().padStart(2, '0')}:${detikLeft.toString().padStart(2, '0')}`;
        } else {
            element.querySelector('.remaining-time').textContent = '00:00:00';
            // Auto refresh ketika jadwal selesai
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    });
}

// Update waktu setiap detik
setInterval(updateWaktu, 1000);
setInterval(updateTimeRemaining, 1000);
updateWaktu();
updateTimeRemaining();

// Auto refresh halaman setiap 1 menit untuk update status dari server
setTimeout(() => {
    location.reload();
}, 60000);
</script>

<!-- CSS Kustom -->
<style>
:root {
    --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-4: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.welcome-title {
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.welcome-text {
    opacity: 0.9;
    margin-bottom: 0;
}

.time-display {
    text-align: right;
}

.current-time {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.live-class-card {
    border: 2px solid #28a745;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
    animation: pulse 2s infinite;
}

.live-indicator {
    width: 12px;
    height: 12px;
    background: #28a745;
    border-radius: 50%;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes pulse {
    0% { box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2); }
    50% { box-shadow: 0 5px 25px rgba(40, 167, 69, 0.4); }
    100% { box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2); }
}

.class-name {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.class-info {
    color: #6c757d;
    font-size: 0.9rem;
}

.section-title {
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-left: 4px solid #667eea;
    padding-left: 1rem;
}

.card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    transition: transform 0.3s ease;
}

.card-link:hover {
    transform: translateY(-5px);
    color: inherit;
    text-decoration: none;
}

.shortcut-card {
    border: none;
    border-radius: 15px;
    color: white;
    transition: all 0.3s ease;
    height: 120px;
    overflow: hidden;
    position: relative;
}

.shortcut-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.gradient-1 { background: var(--gradient-1); }
.gradient-2 { background: var(--gradient-2); }
.gradient-3 { background: var(--gradient-3); }
.gradient-4 { background: var(--gradient-4); }

.shortcut-card .card-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.shortcut-card .card-text {
    opacity: 0.9;
    font-size: 0.85rem;
    margin-bottom: 0;
}

.icon-wrapper {
    font-size: 2.5rem;
    opacity: 0.8;
    margin-left: 1rem;
}

.remaining-time {
    font-size: 1.25rem;
    font-weight: 600;
    color: #dc3545;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #2c3e50;
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-card {
        padding: 1.5rem;
    }
    
    .current-time {
        font-size: 1.5rem;
    }
    
    .icon-wrapper {
        font-size: 2rem;
    }
    
    .shortcut-card {
        height: 100px;
    }
}
</style>
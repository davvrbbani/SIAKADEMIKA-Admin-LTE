<?php
// Langkah 1: Memuat Konfigurasi & Keamanan
require_once '../config.php';
require_login();

// Langkah 2: Mengambil Data dari Sesi & Database
$username = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');

// Inisialisasi variabel count
$jumlah_mahasiswa = 0;
$jumlah_dosen = 0;
$jumlah_kelas = 0; // Menambahkan ini untuk widget baru

try {
    // Langkah 3: Eksekusi Query yang Efisien
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
    
    // Asumsi Anda punya tabel 'kelas'. 
    // Kita abaikan backend, jadi kita set manual saja untuk tampilan.
    $query_kelas = "SELECT COUNT(id) as total_kelas FROM kelas";
    $stmt_kelas = $pdo->query($query_kelas);
    $jumlah_kelas = $stmt_kelas->fetchColumn();

} catch (PDOException $e) {
    // die("Error: " . $e->getMessage());
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <p class="mb-1 fs-5 text-muted">Hai, Selamat Datang <b><?php echo $username; ?>!</b></p>
                    <h3 class="mb-0">Dashboard Admin</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-primary h-100 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1"><?php echo $jumlah_dosen; ?></h1>
                                <p class="card-text mb-0">Total Dosen</p>
                            </div>
                            <i class="bi bi-mortarboard-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=dosen" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-success h-100 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1"><?php echo $jumlah_mahasiswa; ?></h1>
                                <p class="card-text mb-0">Total Mahasiswa</p>
                            </div>
                            <i class="bi bi-people-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=mahasiswa" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-info h-100 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1"><?php echo $jumlah_kelas; ?></h1>
                                <p class="card-text mb-0">Total Kelas</p>
                            </div>
                            <i class="bi bi-book-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=kelas" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="mb-3 text-muted">🚀 Jalan Pintas</h4>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <a href="#" class="btn btn-lg btn-primary w-100 d-flex align-items-center justify-content-center p-3 shadow-sm">
                        <i class="bi bi-person-plus-fill me-2"></i> Tambah Mahasiswa
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <a href="#" class="btn btn-lg btn-outline-primary w-100 d-flex align-items-center justify-content-center p-3">
                        <i class="bi bi-mortarboard me-2"></i> Tambah Dosen
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <a href="#" class="btn btn-lg btn-outline-secondary w-100 d-flex align-items-center justify-content-center p-3">
                        <i class="bi bi-calendar-plus-fill me-2"></i> Atur Jadwal Kuliah
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <a href="#" class="btn btn-lg btn-outline-secondary w-100 d-flex align-items-center justify-content-center p-3">
                        <i class="bi bi-trophy-fill me-2"></i> Input Prestasi
                    </a>
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
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <i class="bi bi-person-plus-fill fs-4 text-success me-3"></i>
                                    <div>
                                        <strong>Admin <?php echo $username; ?></strong> baru saja menambahkan mahasiswa <strong>Budi Santoso</strong>.
                                        <small class="d-block text-muted">2 menit yang lalu</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <i class="bi bi-envelope-open-fill fs-4 text-warning me-3"></i>
                                    <div>
                                        Mahasiswa <strong>Citra Lestari</strong> mengirim <strong>kritik & saran</strong> baru.
                                        <small class="d-block text-muted">1 jam yang lalu</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <i class="bi bi-person-check-fill fs-4 text-primary me-3"></i>
                                    <div>
                                        Data dosen <strong>Dr. Indah</strong> berhasil diperbarui.
                                        <small class="d-block text-muted">3 jam yang lalu</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <i class="bi bi-calendar-event-fill fs-4 text-info me-3"></i>
                                    <div>
                                        Jadwal kuliah <strong>Basis Data</strong> telah dipublikasikan.
                                        <small class="d-block text-muted">1 hari yang lalu</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white text-center py-3">
                            <a href="#" class="text-decoration-none text-primary fw-bold">
                                Lihat Semua Aktivitas <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 pt-3">
                            <h5 class="mb-0"><i class="bi bi-clipboard-data-fill me-2"></i>Laporan Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center p-3 mb-3 bg-light rounded">
                                <i class="bi bi-envelope-exclamation-fill fs-2 text-warning me-3"></i>
                                <div>
                                    <h4 class="mb-0">3</h4> <span class="text-muted">Kritik & Saran Masuk Hari Ini</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="bi bi-trophy-fill fs-2 text-info me-3"></i>
                                <div>
                                    <h4 class="mb-0">8</h4> <span class="text-muted">Prestasi Diinput Bulan Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            </div>
        </div>
    </main>
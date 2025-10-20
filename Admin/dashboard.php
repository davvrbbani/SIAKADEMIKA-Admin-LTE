<?php
// Langkah 1: Memuat Konfigurasi & Keamanan
// Path '../config.php' mengasumsikan file config berada satu level di atas folder 'Admin'.
require_once '../config.php';

// Memastikan hanya user yang sudah login bisa mengakses halaman ini.
// Fungsi require_login() sudah ada di config.php Anda.
require_login();

// Langkah 2: Mengambil Data dari Sesi & Database
// Ambil username dari sesi untuk ditampilkan. Gunakan htmlspecialchars untuk keamanan.
$username = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');

// Inisialisasi variabel count untuk mencegah error jika query gagal
$jumlah_mahasiswa = 0;
$jumlah_dosen = 0;

try {
    // Langkah 3: Eksekusi Query yang Efisien
    // Satu query untuk mengambil total mahasiswa dan dosen menggunakan GROUP BY.
    $query_count = "SELECT role, COUNT(id) as total 
                    FROM users 
                    WHERE role IN ('mahasiswa', 'dosen') 
                    GROUP BY role";
    
    $stmt = $pdo->query($query_count);
    $results = $stmt->fetchAll();

    // Loop hasil query dan masukkan ke variabel PHP
    foreach ($results as $row) {
        if ($row['role'] == 'mahasiswa') {
            $jumlah_mahasiswa = $row['total'];
        } elseif ($row['role'] == 'dosen') {
            $jumlah_dosen = $row['total'];
        }
    }
} catch (PDOException $e) {
    // Jika terjadi error pada database, tampilkan pesan (opsional, baik untuk debugging)
    // die("Error: Tidak dapat mengambil data statistik. " . $e->getMessage());
    // Untuk production, Anda bisa membiarkannya menampilkan 0.
}

?>
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <!-- Kolom Kiri: Judul dan Ucapan Selamat Datang -->
                <div class="col-sm-6">
                    <!-- DATA DINAMIS: Menampilkan username dari sesi -->
                    <p class="mb-1 fs-5 text-muted">Hai, Selamat Datang <b><?php echo $username; ?>!</b></p>
                    <h3 class="mb-0">Dashboard Admin</h3>
                </div>
                <!-- Kolom Kanan: Breadcrumb -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
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
            <!-- Baris untuk Kartu Statistik -->
            <div class="row">

                <!-- Kartu Statistik: Total Dosen -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <!-- DATA DINAMIS: Menampilkan jumlah dosen -->
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

                <!-- Kartu Statistik: Total Mahasiswa -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <!-- DATA DINAMIS: Menampilkan jumlah mahasiswa -->
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

                <!-- Anda bisa menambahkan kartu statistik lain di sini -->
                <!-- Contoh: Kartu Statistik Mata Kuliah -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1">...</h1>
                                <p class="card-text mb-0">Mata Kuliah</p>
                            </div>
                            <i class="bi bi-book-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=matakuliah" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

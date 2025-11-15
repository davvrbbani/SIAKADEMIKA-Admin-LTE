<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// Siapkan variabel untuk re-population
$kelas = '';
$angkatan = date('Y'); // Default angkatan tahun ini

// (Tidak perlu query kelasList)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dan simpan ke variabel
    $kelas = trim($_POST['kelas']);
    $angkatan = trim($_POST['angkatan']);

    // Validasi
    if ($kelas === '' || $angkatan === '' || !is_numeric($angkatan)) {
        $error_message = 'Nama Kelas dan Angkatan wajib diisi, dan Angkatan harus berupa angka!';
    } else {
        try {
            $pdo->beginTransaction();

            // Simpan ke tabel kelas
            $insertKelas = $pdo->prepare("
                INSERT INTO kelas (kelas, angkatan)
                VALUES (?, ?)
            ");
            $insertKelas->execute([$kelas, $angkatan]);

            $pdo->commit();
            $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
            $pesan_log = "Menambahkan Kelas baru: $kelas (Angkatan: $angkatan)";
            log_activity($pdo, $admin_id, $pesan_log);
            echo "<script>
                    alert('✅ Data kelas berhasil ditambahkan!'); 
                    window.location.href='./?p=kelas';
                  </script>";
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Gagal menyimpan data: " . addslashes($e.getMessage());
        }
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Kelas</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=kelas">Data Kelas</a></li>
                        <li class="breadcrumb-item active">Tambah Data</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <?php if (!empty($error_message)): ?>
                        <script>
                            alert('<?php echo $error_message; ?>');
                        </script>
                    <?php endif; ?>

                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Form Tambah Kelas</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kelas</label>
                                    <input type="text" name="kelas" class="form-control" required
                                           value="<?= htmlspecialchars($kelas) ?>" placeholder="Contoh: INF A">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Angkatan</label>
                                    <input type="number" name="angkatan" class="form-control" required
                                           value="<?= htmlspecialchars($angkatan) ?>" placeholder="Contoh: 2023">
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <a href="?p=kelas" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
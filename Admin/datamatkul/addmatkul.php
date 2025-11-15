<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
$kode_mk = '';
$nama_mk = '';
$sks = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $sks = intval($_POST['sks']); // Ambil sebagai integer

    // Validasi
    if ($kode_mk === '' || $nama_mk === '' || $sks <= 0) {
        $error_message = 'Semua field wajib diisi dan SKS harus lebih dari 0!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Query insert langsung ke mata_kuliah
            $insertMatkul = $pdo->prepare("
                INSERT INTO mata_kuliah (kode_mk, nama_mk, sks)
                VALUES (?, ?, ?)
            ");
            $insertMatkul->execute([$kode_mk, $nama_mk, $sks]);
            
            $pdo->commit();
            $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
            $pesan_log = "menambahkan Matakuliah baru: $nama_mk (Kode Matkul: $kode_mk)";
            log_activity($pdo, $admin_id, $pesan_log);
            echo "<script>
                    alert('✅ Data mata kuliah berhasil ditambahkan!'); 
                    window.location.href='./?p=matakuliah';
                  </script>";
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Gagal menyimpan data: " . addslashes($e->getMessage());
        }
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Mata Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=matakuliah">Mata Kuliah</a></li>
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
                            <h4 class="mb-0">Form Tambah Mata Kuliah</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Kode Mata Kuliah</label>
                                    <input type="text" name="kode_mk" class="form-control" required
                                           value="<?= htmlspecialchars($kode_mk) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Mata Kuliah</label>
                                    <input type="text" name="nama_mk" class="form-control" required
                                           value="<?= htmlspecialchars($nama_mk) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jumlah SKS</label>
                                    <input type="number" name="sks" class="form-control" required
                                           value="<?= htmlspecialchars($sks) ?>" min="1" max="10">
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <a href="?p=matakuliah" class="btn btn-secondary">
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
<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// Variabel untuk re-population
$mahasiswa_id = 0;
$judul_prestasi = '';
$deskripsi = '';
$tingkat = 'Internal';
$tanggal_diraih = date('Y-m-d');

// Data untuk mengisi Dropdown Mahasiswa
$mahasiswaList = [];
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap, nim FROM mahasiswa ORDER BY nama_lengkap ASC");
    $mahasiswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Gagal memuat daftar mahasiswa: " . addslashes($e->getMessage());
}

// Daftar Tingkat (dari ENUM)
$tingkat_options = ['Internal', 'Regional', 'Nasional', 'Internasional'];

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data (untuk re-population jika error)
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $judul_prestasi = trim($_POST['judul_prestasi']);
    $deskripsi = trim($_POST['deskripsi']);
    $tingkat = trim($_POST['tingkat']);
    $tanggal_diraih = trim($_POST['tanggal_diraih']);
    // Asumsi ID admin ada di session
    $input_by_user_id = $_SESSION['user_id'] ?? 1; // Ganti '1' dengan ID Admin default jika session tidak ada

    try {
        if ($mahasiswa_id <= 0 || $judul_prestasi === '' || $tanggal_diraih === '') {
            throw new Exception("Mahasiswa, Judul Prestasi, dan Tanggal wajib diisi.");
        }
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO prestasi_mahasiswa (mahasiswa_id, judul_prestasi, deskripsi, tingkat, tanggal_diraih, input_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mahasiswa_id, $judul_prestasi, $deskripsi, $tingkat, $tanggal_diraih, $input_by_user_id]);
        
        $pdo->commit();
        echo "<script>
                alert('✅ Prestasi berhasil ditambahkan!'); 
                window.location.href='./?p=manage-prestasi';
              </script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Prestasi</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=manage-prestasi">Manajemen Prestasi</a></li>
                        <li class="breadcrumb-item active">Tambah Prestasi</li>
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
                            <h4 class="mb-0">Form Tambah Prestasi</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                
                                <div class="mb-3">
                                    <label class="form-label">Mahasiswa</label>
                                    <select name="mahasiswa_id" class="form-select" required>
                                        <option value="">-- Pilih Mahasiswa --</option>
                                        <?php foreach ($mahasiswaList as $mhs): ?>
                                            <option value="<?= $mhs['id'] ?>" <?= ($mahasiswa_id == $mhs['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($mhs['nama_lengkap'] . ' (' . $mhs['nim'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Judul Prestasi/Kejuaraan</label>
                                    <input type="text" name="judul_prestasi" class="form-control" 
                                           value="<?= htmlspecialchars($judul_prestasi) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tingkat</label>
                                    <select name="tingkat" class="form-select" required>
                                        <?php foreach ($tingkat_options as $option): ?>
                                            <option value="<?= $option ?>" <?= ($tingkat == $option) ? 'selected' : '' ?>>
                                                <?= $option ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Diraih</label>
                                    <input type="date" name="tanggal_diraih" class="form-control" 
                                           value="<?= htmlspecialchars($tanggal_diraih) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi (Opsional)</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=manage-prestasi" class="btn btn-secondary">
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
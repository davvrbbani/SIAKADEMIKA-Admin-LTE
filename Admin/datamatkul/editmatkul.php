<?php
require_once "../config.php";

$error_message = ''; // 1. Siapkan variabel error
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    // Sesuaikan error message dan redirect
    echo "<script>alert('ID mata kuliah tidak valid!'); window.location.href='?p=matakuliah';</script>";
    exit;
}

// 2. Siapkan variabel untuk menampung data (sesuai tabel mata_kuliah)
$kode_mk = '';
$nama_mk = '';
$sks = '';

try {
    // 3. Ambil data awal dari DB (hanya dari tabel mata_kuliah)
    $stmt = $pdo->prepare("
        SELECT *
        FROM mata_kuliah
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $matkul_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$matkul_data) {
        echo "<script>alert('Data mata kuliah tidak ditemukan!'); window.location.href='?p=matakuliah';</script>";
        exit;
    }
    
    // (Tidak perlu $user_id_for_update)
    // (Tidak perlu $kelasList)

    // 4. Proses Form Jika di-Submit (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari POST
        $kode_mk = trim($_POST['kode_mk']);
        $nama_mk = trim($_POST['nama_mk']);
        $sks = trim($_POST['sks']);

        // Validasi
        if ($kode_mk === '' || $nama_mk === '' || $sks === '' || !is_numeric($sks) || intval($sks) <= 0) {
            $error_message = 'Semua field wajib diisi dan SKS harus angka lebih dari 0!';
        } else {
            // Lolos validasi, coba update
            $pdo->beginTransaction();
            try {
                // Update tabel mata_kuliah
                $updateMatkul = $pdo->prepare("
                    UPDATE mata_kuliah 
                    SET kode_mk = ?, nama_mk = ?, sks = ?
                    WHERE id = ?
                ");
                $updateMatkul->execute([$kode_mk, $nama_mk, $sks, $id]);
                
                // (Tidak ada update tabel users)

                $pdo->commit();
                $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
                $pesan_log = "memperbarui data Matakuliah: $nama_mk (Kode Matkul: $kode_mk)";
                log_activity($pdo, $admin_id, $pesan_log);
            
                // SUKSES: Alert dan redirect
                echo "
                <script>
                    alert('Data mata kuliah berhasil diperbarui!');
                    window.location.href = '?p=matakuliah';
                </script>";
                exit;

            } catch (Exception $e) {
                // GAGAL: Rollback dan set error message
                $pdo->rollBack();
                $error_message = "Gagal menyimpan data: " . addslashes($e->getMessage());
            }
        }
    } else {
        // 5. Jika bukan POST (artinya GET), isi variabel dari data DB
        $kode_mk = $matkul_data['kode_mk'];
        $nama_mk = $matkul_data['nama_mk'];
        $sks = $matkul_data['sks'];
    }
} catch (PDOException $e) {
    // Gagal saat mengambil data awal (GET)
    echo "<script>alert('Database error saat memuat: " . addslashes($e->getMessage()) . "'); window.location.href='?p=matakuliah';</script>";
    exit;
}
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Edit Mata Kuliah</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=matakuliah">Mata Kuliah</a></li>
            <li class="breadcrumb-item active">Edit Mata Kuliah</li>
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
              <h4 class="mb-0">Form Edit Mata Kuliah</h4>
            </div>
            <div class="card-body">
              <form method="POST">
                
                <div class="mb-3">
                  <label class="form-label">Kode Mata Kuliah</label>
                  <input type="text" name="kode_mk" class="form-control" value="<?= htmlspecialchars($kode_mk) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nama Mata Kuliah</label>
                  <input type="text" name="nama_mk" class="form-control" value="<?= htmlspecialchars($nama_mk) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">SKS</label>
                  <input type="number" name="sks" class="form-control" value="<?= htmlspecialchars($sks) ?>" required min="1">
                </div>

                <div class="d-flex justify-content-between mt-4">
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
<?php
require_once "../config.php";

$error_message = ''; // 1. Siapkan variabel error
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID kelas tidak valid!'); window.location.href='?p=kelas';</script>";
    exit;
}

// 2. Siapkan variabel untuk menampung data
$kelas = '';
$angkatan = '';

try {
    // 3. Ambil data awal dari DB
    $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $kelas_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kelas_data) {
        echo "<script>alert('Data kelas tidak ditemukan!'); window.location.href='?p=kelas';</script>";
        exit;
    }
    
    // (Tidak perlu $kelasList)

    // 4. Proses Form Jika di-Submit (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari POST
        $kelas = trim($_POST['kelas']);
        $angkatan = trim($_POST['angkatan']);

        // Validasi
        if ($kelas === '' || $angkatan === '' || !is_numeric($angkatan)) {
            $error_message = 'Nama Kelas dan Angkatan wajib diisi, dan Angkatan harus berupa angka!';
        } else {
            // Lolos validasi, coba update
            $pdo->beginTransaction();
            try {
                // Update tabel kelas
                $updateKelas = $pdo->prepare("
                    UPDATE kelas 
                    SET kelas = ?, angkatan = ?
                    WHERE id = ?
                ");
                $updateKelas->execute([$kelas, $angkatan, $id]);
                
                $pdo->commit();
                $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
                $pesan_log = "Memperbarui Data Kelas: $kelas (Angkatan: $angkatan)";
                log_activity($pdo, $admin_id, $pesan_log);
                // SUKSES: Alert dan redirect
                echo "
                <script>
                    alert('Data kelas berhasil diperbarui!');
                    window.location.href = '?p=kelas';
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
        $kelas = $kelas_data['kelas'];
        $angkatan = $kelas_data['angkatan'];
    }
} catch (PDOException $e) {
    // Gagal saat mengambil data awal (GET)
    echo "<script>alert('Database error saat memuat: " . addslashes($e->getMessage()) . "'); window.location.href='?p=kelas';</script>";
    exit;
}
?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Edit Kelas</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item"><a href="?p=kelas">Data Kelas</a></li>
            <li class="breadcrumb-item active">Edit Kelas</li>
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
              <h4 class="mb-0">Form Edit Kelas</h4>
            </div>
            <div class="card-body">
              <form method="POST">
                
                <div class="mb-3">
                  <label class="form-label">Nama Kelas</label>
                  <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($kelas) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Angkatan</label>
                  <input type="number" name="angkatan" class="form-control" value="<?= htmlspecialchars($angkatan) ?>" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
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
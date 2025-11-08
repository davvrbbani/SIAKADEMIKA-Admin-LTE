<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// 1. Ambil ID jadwal dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID jadwal tidak valid!'); window.location.href='?p=jadwal-kuliah';</script>";
    exit;
}

// 2. Siapkan variabel untuk menampung data (re-population)
$mata_kuliah_id = 0;
$dosen_id = 0;
$kelas_id = 0;
$hari = '';
$jam_mulai = '';
$jam_selesai = '';
$ruangan = '';

// 3. Ambil data master untuk 3 dropdown
try {
    $matkulStmt = $pdo->query("SELECT id, nama_mk, kode_mk FROM mata_kuliah ORDER BY nama_mk ASC");
    $matkulList = $matkulStmt->fetchAll(PDO::FETCH_ASSOC);

    $dosenStmt = $pdo->query("SELECT id, nama_lengkap FROM dosen ORDER BY nama_lengkap ASC");
    $dosenList = $dosenStmt->fetchAll(PDO::FETCH_ASSOC);

    $kelasStmt = $pdo->query("SELECT id, kelas, angkatan FROM kelas ORDER BY angkatan DESC, kelas ASC");
    $kelasList = $kelasStmt->fetchAll(PDO::FETCH_ASSOC);

    // Daftar hari (sesuai ENUM)
    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

} catch (PDOException $e) {
    $error_message = "Gagal memuat data master: " . addslashes($e->getMessage());
    $matkulList = [];
    $dosenList = [];
    $kelasList = [];
}

// 4. Proses Form Jika di-Submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form POST (untuk update ATAU re-populate)
    $mata_kuliah_id = intval($_POST['mata_kuliah_id']);
    $dosen_id = intval($_POST['dosen_id']);
    $kelas_id = intval($_POST['kelas_id']);
    $hari = trim($_POST['hari']);
    $jam_mulai = trim($_POST['jam_mulai']);
    $jam_selesai = trim($_POST['jam_selesai']);
    $ruangan = trim($_POST['ruangan']);

    // Validasi
    if ($mata_kuliah_id <= 0 || $dosen_id <= 0 || $kelas_id <= 0 || $hari === '' || $jam_mulai === '' || $jam_selesai === '' || $ruangan === '') {
        $error_message = 'Semua field wajib diisi!';
    } elseif ($jam_selesai <= $jam_mulai) {
        $error_message = 'Jam Selesai harus setelah Jam Mulai!';
    } else {
        try {
            $pdo->beginTransaction();

            // Cek bentrok (Mirip 'add', tapi kecualikan ID jadwal ini sendiri 'AND id != ?')
            $checkStmt = $pdo->prepare("
                SELECT id FROM jadwal_kuliah 
                WHERE hari = ? AND ruangan = ? AND id != ?
                AND (
                    (jam_mulai < ? AND jam_selesai > ?) OR
                    (jam_mulai >= ? AND jam_mulai < ?) OR
                    (jam_selesai > ? AND jam_selesai <= ?)
                )
            ");
            $checkStmt->execute([
                $hari, $ruangan, $id, // Tambahkan $id di sini
                $jam_selesai, $jam_mulai,
                $jam_mulai, $jam_selesai,
                $jam_mulai, $jam_selesai
            ]);
            
            if ($checkStmt->fetch()) {
                 $error_message = 'Gagal: Jadwal bentrok! Ruangan dan Waktu sudah terpakai oleh jadwal lain.';
                 $pdo->rollBack();
            } else {
                // Jika tidak bentrok, UPDATE
                $updateStmt = $pdo->prepare("
                    UPDATE jadwal_kuliah SET
                        mata_kuliah_id = ?, 
                        dosen_id = ?, 
                        kelas_id = ?, 
                        hari = ?, 
                        jam_mulai = ?, 
                        jam_selesai = ?, 
                        ruangan = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$mata_kuliah_id, $dosen_id, $kelas_id, $hari, $jam_mulai, $jam_selesai, $ruangan, $id]);
                
                $pdo->commit();

                echo "<script>
                        alert('✅ Data jadwal kuliah berhasil diperbarui!'); 
                        window.location.href='./?p=jadwal-kuliah';
                      </script>";
                exit;
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = "Gagal memperbarui data: " . addslashes($e->getMessage());
        }
    }
} else {
    // 5. Jika bukan POST (method GET), ambil data jadwal dari DB untuk isi form
    try {
        $stmt = $pdo->prepare("SELECT * FROM jadwal_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$jadwal) {
            echo "<script>alert('Data jadwal tidak ditemukan!'); window.location.href='?p=jadwal-kuliah';</script>";
            exit;
        }

        // Isi variabel dari database
        $mata_kuliah_id = $jadwal['mata_kuliah_id'];
        $dosen_id = $jadwal['dosen_id'];
        $kelas_id = $jadwal['kelas_id'];
        $hari = $jadwal['hari'];
        $jam_mulai = $jadwal['jam_mulai'];
        $jam_selesai = $jadwal['jam_selesai'];
        $ruangan = $jadwal['ruangan'];

    } catch (PDOException $e) {
        $error_message = "Gagal memuat data jadwal: " . addslashes($e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Edit Jadwal Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=jadwal-kuliah">Jadwal Kuliah</a></li>
                        <li class="breadcrumb-item active">Edit Jadwal</li>
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
                            <h4 class="mb-0">Form Edit Jadwal</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Mata Kuliah</label>
                                    <select name="mata_kuliah_id" class="form-select" required>
                                        <option value="">-- Pilih Mata Kuliah --</option>
                                        <?php foreach ($matkulList as $mk): ?>
                                            <option value="<?= $mk['id'] ?>" <?= ($mata_kuliah_id == $mk['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($mk['kode_mk'] . ' - ' . $mk['nama_mk']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dosen Pengampu</label>
                                    <select name="dosen_id" class="form-select" required>
                                        <option value="">-- Pilih Dosen --</option>
                                        <?php foreach ($dosenList as $d): ?>
                                            <option value="<?= $d['id'] ?>" <?= ($dosen_id == $d['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($d['nama_lengkap']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($kelasList as $k): ?>
                                            <option value="<?= $k['id'] ?>" <?= ($kelas_id == $k['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($k['kelas'] . ' (Angk. ' . $k['angkatan'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hari</label>
                                        <select name="hari" class="form-select" required>
                                            <option value="">-- Pilih Hari --</option>
                                            <?php foreach ($hariList as $h): ?>
                                                <option value="<?= $h ?>" <?= ($hari == $h) ? 'selected' : '' ?>>
                                                    <?= $h ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ruangan</label>
                                        <input type="text" name="ruangan" class="form-control" 
                                               value="<?= htmlspecialchars($ruangan) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" class="form-control" 
                                               value="<?= htmlspecialchars($jam_mulai) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" class="form-control" 
                                               value="<?= htmlspecialchars($jam_selesai) ?>" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=jadwal-kuliah" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Simpan Perubahan
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
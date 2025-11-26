<?php
require_once "../config.php"; 

$error_message = '';
$mahasiswa_id = 0;
$judul_prestasi = '';
$deskripsi = '';
$tingkat = 'Internal';
$tanggal_diraih = date('Y-m-d');

// Data Dropdown Mahasiswa
$mahasiswaList = [];
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap, nim FROM mahasiswa ORDER BY nama_lengkap ASC");
    $mahasiswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Gagal: " . addslashes($e->getMessage());
}

$tingkat_options = ['Internal', 'Regional', 'Nasional', 'Internasional'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $judul_prestasi = trim($_POST['judul_prestasi']);
    $deskripsi = trim($_POST['deskripsi']);
    $tingkat = trim($_POST['tingkat']);
    $tanggal_diraih = trim($_POST['tanggal_diraih']);
    $input_by_user_id = $_SESSION['user_id'] ?? 1; 

    try {
        if ($mahasiswa_id <= 0 || $judul_prestasi === '' || $tanggal_diraih === '') {
            throw new Exception("Mahasiswa, Judul, dan Tanggal wajib diisi.");
        }

        // --- LOGIKA UPLOAD FOTO ---
        $foto_bukti_path = NULL;
        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf']; // Boleh PDF atau Gambar
            $filename = $_FILES['foto_bukti']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $size = $_FILES['foto_bukti']['size'];

            if (!in_array($ext, $allowed)) throw new Exception("Format file harus JPG, PNG, atau PDF.");
            if ($size > 2 * 1024 * 1024) throw new Exception("Ukuran file maksimal 2MB.");

            // Pastikan folder ada
            $target_dir = "../uploads/prestasi/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            // Nama file unik: prestasi_IDMHS_TIMESTAMP.ext
            $new_filename = "prestasi_" . $mahasiswa_id . "_" . time() . "." . $ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $target_file)) {
                $foto_bukti_path = "uploads/prestasi/" . $new_filename; // Path untuk DB
            } else {
                throw new Exception("Gagal mengupload file.");
            }
        }
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO prestasi_mahasiswa (mahasiswa_id, judul_prestasi, deskripsi, tingkat, tanggal_diraih, foto_bukti, input_by_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mahasiswa_id, $judul_prestasi, $deskripsi, $tingkat, $tanggal_diraih, $foto_bukti_path, $input_by_user_id]);
        
        $pdo->commit();
        echo "<script>alert('✅ Prestasi berhasil ditambahkan!'); window.location.href='./?p=manage-prestasi';</script>";
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
            <h3>Tambah Prestasi</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white"><h4 class="mb-0">Form Tambah</h4></div>
                <div class="card-body">
                    <?php if($error_message): ?><div class="alert alert-danger"><?= $error_message ?></div><?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
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
                            <label class="form-label">Judul Prestasi</label>
                            <input type="text" name="judul_prestasi" class="form-control" value="<?= htmlspecialchars($judul_prestasi) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tingkat</label>
                                <select name="tingkat" class="form-select" required>
                                    <?php foreach ($tingkat_options as $option): ?>
                                        <option value="<?= $option ?>" <?= ($tingkat == $option) ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Diraih</label>
                                <input type="date" name="tanggal_diraih" class="form-control" value="<?= htmlspecialchars($tanggal_diraih) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Bukti / Sertifikat</label>
                            <input type="file" name="foto_bukti" class="form-control" accept="image/*,application/pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF (Maks 2MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="?p=manage-prestasi" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
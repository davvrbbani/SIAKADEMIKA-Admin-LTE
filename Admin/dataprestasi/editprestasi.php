<?php
require_once "../config.php"; 

$error_message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo "<script>window.location.href='?p=manage-prestasi';</script>"; exit; }

// Data Awal
$mahasiswa_id = 0; $judul_prestasi = ''; $deskripsi = ''; $tingkat = ''; $tanggal_diraih = ''; $foto_lama = '';

// Load Data Mahasiswa
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap, nim FROM mahasiswa ORDER BY nama_lengkap ASC");
    $mahasiswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $error_message = $e->getMessage(); }
$tingkat_options = ['Internal', 'Regional', 'Nasional', 'Internasional'];

// PROSES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = intval($_POST['mahasiswa_id']);
    $judul_prestasi = trim($_POST['judul_prestasi']);
    $deskripsi = trim($_POST['deskripsi']);
    $tingkat = trim($_POST['tingkat']);
    $tanggal_diraih = trim($_POST['tanggal_diraih']);
    $foto_lama_path = $_POST['foto_lama_path']; // Path lama dari hidden input

    try {
        $pdo->beginTransaction();
        
        // Logic Upload Foto Baru
        $foto_path_final = $foto_lama_path; // Default pakai yang lama

        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $ext = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) throw new Exception("Format file salah.");
            
            $target_dir = "../uploads/prestasi/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $new_filename = "prestasi_" . $mahasiswa_id . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $target_dir . $new_filename)) {
                // Hapus foto lama jika ada
                if (!empty($foto_lama_path) && file_exists("../" . $foto_lama_path)) {
                    unlink("../" . $foto_lama_path);
                }
                $foto_path_final = "uploads/prestasi/" . $new_filename;
            }
        }

        $sql = "UPDATE prestasi_mahasiswa SET mahasiswa_id=?, judul_prestasi=?, deskripsi=?, tingkat=?, tanggal_diraih=?, foto_bukti=? WHERE id=?";
        $pdo->prepare($sql)->execute([$mahasiswa_id, $judul_prestasi, $deskripsi, $tingkat, $tanggal_diraih, $foto_path_final, $id]);
        
        $pdo->commit();
        echo "<script>alert('✅ Update berhasil!'); window.location.href='./?p=manage-prestasi';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
    }
} else {
    // GET DATA
    $stmt = $pdo->prepare("SELECT * FROM prestasi_mahasiswa WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if($data) {
        $mahasiswa_id = $data['mahasiswa_id'];
        $judul_prestasi = $data['judul_prestasi'];
        $deskripsi = $data['deskripsi'];
        $tingkat = $data['tingkat'];
        $tanggal_diraih = $data['tanggal_diraih'];
        $foto_lama = $data['foto_bukti'];
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3>Edit Prestasi</h3></div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark"><h4 class="mb-0">Form Edit</h4></div>
                <div class="card-body">
                    <?php if($error_message): ?><div class="alert alert-danger"><?= $error_message ?></div><?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="foto_lama_path" value="<?= htmlspecialchars($foto_lama) ?>">

                        <div class="mb-3">
                            <label>Mahasiswa</label>
                            <select name="mahasiswa_id" class="form-select" required>
                                <?php foreach ($mahasiswaList as $mhs): ?>
                                    <option value="<?= $mhs['id'] ?>" <?= ($mahasiswa_id == $mhs['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mhs['nama_lengkap'] . ' (' . $mhs['nim'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Judul Prestasi</label>
                            <input type="text" name="judul_prestasi" class="form-control" value="<?= htmlspecialchars($judul_prestasi) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Tingkat</label>
                                <select name="tingkat" class="form-select">
                                    <?php foreach ($tingkat_options as $option): ?>
                                        <option value="<?= $option ?>" <?= ($tingkat == $option) ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal_diraih" class="form-control" value="<?= htmlspecialchars($tanggal_diraih) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Foto Bukti</label>
                            <input type="file" name="foto_bukti" class="form-control">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                            
                            <?php if (!empty($foto_lama)): ?>
                                <div class="mt-2 p-2 border rounded bg-light">
                                    <small class="d-block mb-1">File Saat Ini:</small>
                                    <?php $ext = pathinfo($foto_lama, PATHINFO_EXTENSION); ?>
                                    <?php if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                        <img src="../<?= htmlspecialchars($foto_lama) ?>" alt="Bukti" style="max-height: 150px; border-radius: 5px;">
                                    <?php else: ?>
                                        <a href="../<?= htmlspecialchars($foto_lama) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-file-download"></i> Lihat File (<?= $ext ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="?p=manage-prestasi" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
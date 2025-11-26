<?php
require_once "../config.php"; 

$error_message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID postingan tidak valid!'); window.location.href='?p=manage-kritik';</script>";
    exit;
}

// Variabel Awal
$judul = '';
$isi = '';
$foto_lama = '';

// --- PROSES UPDATE (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $foto_lama_path = $_POST['foto_lama_path']; // Path dari hidden input

    try {
        if ($isi === '') throw new Exception("Isi tidak boleh kosong.");
        
        $pdo->beginTransaction();

        // 1. LOGIKA UPLOAD FOTO BARU
        $foto_path_final = $foto_lama_path; // Default pakai yang lama

        if (isset($_FILES['foto_lampiran']) && $_FILES['foto_lampiran']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['foto_lampiran']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) throw new Exception("Format foto harus JPG atau PNG.");
            if ($_FILES['foto_lampiran']['size'] > 2 * 1024 * 1024) throw new Exception("Maksimal ukuran 2MB.");

            $target_dir = "../uploads/kritik/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            // Nama unik admin edit
            $new_filename = "kritik_edit_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['foto_lampiran']['tmp_name'], $target_dir . $new_filename)) {
                // Upload sukses, hapus foto lama
                if (!empty($foto_lama_path) && file_exists("../" . $foto_lama_path)) {
                    unlink("../" . $foto_lama_path);
                }
                // Set path baru untuk database
                $foto_path_final = "uploads/kritik/" . $new_filename;
            }
        }
        
        // 2. UPDATE DATABASE
        // Admin bisa edit Judul, Isi, dan Foto
        $sql = "UPDATE kritik_saran SET judul = ?, isi = ?, foto_lampiran = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$judul, $isi, $foto_path_final, $id]);
        
        $pdo->commit();
        echo "<script>alert('✅ Postingan berhasil diperbarui!'); window.location.href='./?p=detail-kritik-admin&id=$id';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
    }
    
} else {
    // --- AMBIL DATA LAMA (GET) ---
    try {
        $stmt = $pdo->prepare("SELECT * FROM kritik_saran WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "<script>alert('Data tidak ditemukan!'); window.location.href='?p=manage-kritik';</script>";
            exit;
        }

        $judul = $post['judul'];
        $isi = $post['isi'];
        $foto_lama = $post['foto_lampiran'];

    } catch (PDOException $e) {
        $error_message = "Gagal memuat data: " . addslashes($e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3>Edit Postingan</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=manage-kritik">Forum</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <div class="alert alert-danger"><?= $error_message ?></div>
                    <?php endif; ?>

                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0">Form Edit</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="foto_lama_path" value="<?= htmlspecialchars($foto_lama) ?>">

                                <div class="mb-3">
                                    <label class="form-label">Judul Postingan</label>
                                    <input type="text" name="judul" class="form-control" 
                                           value="<?= htmlspecialchars($judul) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Isi Postingan</label>
                                    <textarea name="isi" class="form-control" rows="8" required><?= htmlspecialchars($isi) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Foto Lampiran</label>
                                    <input type="file" name="foto_lampiran" class="form-control" accept="image/*">
                                    <div class="form-text">Upload foto baru jika ingin mengganti yang lama.</div>

                                    <?php if (!empty($foto_lama) && file_exists("../" . $foto_lama)): ?>
                                        <div class="mt-3 p-2 border rounded bg-light d-inline-block">
                                            <p class="mb-1 small fw-bold">Foto Saat Ini:</p>
                                            <img src="../<?= htmlspecialchars($foto_lama) ?>" alt="Foto Lama" style="max-height: 150px; border-radius: 5px;">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=manage-kritik" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Simpan Perubahan
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
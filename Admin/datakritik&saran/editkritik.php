<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID postingan tidak valid!'); window.location.href='?p=manage-kritik';</script>";
    exit;
}

// Variabel
$judul = '';
$isi = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);

    try {
        if ($isi === '') {
            throw new Exception("Isi tidak boleh kosong.");
        }
        
        $pdo->beginTransaction();
        
        // Admin HANYA boleh edit judul dan isi
        $sql = "UPDATE kritik_saran SET judul = ?, isi = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$judul, $isi, $id]);
        
        $pdo->commit();
        echo "<script>
                alert('✅ Postingan berhasil diperbarui!'); 
                window.location.href='./?p=detail-kritik-admin&id=$id';
              </script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
    }
    
} else {
    // Ambil data (method GET)
    try {
        $stmt = $pdo->prepare("SELECT * FROM kritik_saran WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "<script>alert('Data postingan tidak ditemukan!'); window.location.href='?p=manage-kritik';</script>";
            exit;
        }

        $judul = $post['judul'];
        $isi = $post['isi'];

    } catch (PDOException $e) {
        $error_message = "Gagal memuat data: " . addslashes($e->getMessage());
    }
}
?>

<!--begin::App Main-->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Edit Postingan (Admin)</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=manage-kritik">Forum & Kritik</a></li>
                        <li class="breadcrumb-item active">Edit Postingan</li>
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
                            <h4 class="mb-0">Form Edit Postingan</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Judul Postingan</label>
                                    <input type="text" name="judul" class="form-control" 
                                           value="<?= htmlspecialchars($judul) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Isi Postingan</label>
                                    <textarea name="isi" class="form-control" rows="10" required><?= htmlspecialchars($isi) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=manage-kritik&id=<?= $id ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Batal
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
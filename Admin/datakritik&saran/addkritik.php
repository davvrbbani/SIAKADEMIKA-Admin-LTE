<?php
require_once "../config.php"; 

$error_message = '';
$judul = '';
$isi = '';
$parent_id = NULL;
$tipe = 'Publik'; 
$admin_user_id = $_SESSION['user_id'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = $_POST['parent_id'] ? intval($_POST['parent_id']) : NULL;
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $tipe = trim($_POST['tipe'] ?? 'Publik');
    $is_anonim = intval($_POST['is_anonim'] ?? 0);
    $target_dosen_id = NULL; 
    $foto_path = NULL;

    try {
        if ($isi === '') throw new Exception("Isi postingan tidak boleh kosong.");
        
        // LOGIKA UPLOAD FOTO
        if (isset($_FILES['foto_lampiran']) && $_FILES['foto_lampiran']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['foto_lampiran']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) throw new Exception("Format foto harus JPG/PNG.");
            if ($_FILES['foto_lampiran']['size'] > 2 * 1024 * 1024) throw new Exception("Maksimal ukuran 2MB.");

            $target_dir = "../uploads/kritik/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $new_filename = "kritik_admin_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['foto_lampiran']['tmp_name'], $target_dir . $new_filename)) {
                $foto_path = "uploads/kritik/" . $new_filename;
            }
        }
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO kritik_saran (parent_id, user_id, tipe, is_anonim, target_dosen_id, judul, isi, foto_lampiran) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$parent_id, $admin_user_id, $tipe, $is_anonim, $target_dosen_id, $judul, $isi, $foto_path]);
        
        $pdo->commit();

        if ($parent_id) {
            echo "<script>alert('Balasan terkirim!'); window.location.href='./?p=detail-kritik-admin&id=$parent_id';</script>";
        } else {
            echo "<script>alert('Postingan berhasil dibuat!'); window.location.href='./?p=manage-kritik';</script>";
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
        if ($parent_id) {
            echo "<script>alert('$error_message'); window.history.back();</script>";
        }
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3>Buat Postingan Forum (Admin)</h3></div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white"><h4 class="mb-0">Form Postingan</h4></div>
                <div class="card-body">
                    <?php if($error_message): ?><div class="alert alert-danger"><?= $error_message ?></div><?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="tipe" value="Publik">
                        <input type="hidden" name="is_anonim" value="0">

                        <div class="mb-3">
                            <label class="form-label">Judul Postingan</label>
                            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($judul) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Postingan</label>
                            <textarea name="isi" class="form-control" rows="5" required><?= htmlspecialchars($isi) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lampiran Foto (Opsional)</label>
                            <input type="file" name="foto_lampiran" class="form-control" accept="image/*">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="?p=manage-kritik" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
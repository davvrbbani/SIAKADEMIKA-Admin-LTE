<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// Variabel untuk re-population
$judul = '';
$isi = '';
$parent_id = NULL;
$tipe = 'Publik'; // Default

// Ambil ID Admin dari session
$admin_user_id = $_SESSION['user_id'] ?? 1; // Ganti '1' dengan ID Admin default jika session tidak ada

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data
    $parent_id = $_POST['parent_id'] ? intval($_POST['parent_id']) : NULL;
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $tipe = trim($_POST['tipe'] ?? 'Publik'); // Ambil tipe dari form balasan
    $is_anonim = intval($_POST['is_anonim'] ?? 0); // Admin default tidak anonim
    
    // (Admin tidak bisa submit ke 'Personal' atau 'target_dosen_id' dari sini)
    $target_dosen_id = NULL; 

    try {
        if ($isi === '') {
            throw new Exception("Isi postingan tidak boleh kosong.");
        }
        
        $pdo->beginTransaction();

        $sql = "INSERT INTO kritik_saran (parent_id, user_id, tipe, is_anonim, target_dosen_id, judul, isi) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$parent_id, $admin_user_id, $tipe, $is_anonim, $target_dosen_id, $judul, $isi]);
        
        $pdo->commit();

        // Redirect kembali ke halaman yang sesuai
        if ($parent_id) {
            // Jika ini balasan, kembali ke halaman detail
            echo "<script>alert('Balasan berhasil dikirim!'); window.location.href='./?p=detail-kritik-admin&id=$parent_id';</script>";
        } else {
            // Jika ini postingan baru, kembali ke daftar utama
            echo "<script>alert('Postingan berhasil dibuat!'); window.location.href='./?p=manage-kritik';</script>";
        }
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
        
        // Jika error, kembali ke halaman sebelumnya
        if ($parent_id) {
            echo "<script>alert('$error_message'); window.location.href='./?p=detail-kritik-admin&id=$parent_id';</script>";
        } else {
            // Kita perlu re-populate form, jadi kita butuh layout HTML
            // (Lihat file 'add-prestasi-admin.php' untuk contoh form lengkap)
             echo "<script>alert('$error_message'); window.history.back();</script>";
        }
        exit;
    }
}
?>

<!-- Tampilan Form (Hanya untuk postingan baru, balasan ada di 'detail') -->
<!--begin::App Main-->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Buat Postingan Forum (Admin)</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=manage-kritik">Forum & Kritik</a></li>
                        <li class="breadcrumb-item active">Buat Postingan</li>
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
                            <h4 class="mb-0">Form Postingan Publik (Pengumuman)</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <!-- Admin hanya bisa post Tipe 'Publik' dari sini -->
                                <input type="hidden" name="tipe" value="Publik">
                                <input type="hidden" name="parent_id" value=""> <!-- Kosong = Postingan Baru -->
                                <input type="hidden" name="is_anonim" value="0"> <!-- Admin tidak anonim -->

                                <div class="mb-3">
                                    <label class="form-label">Judul Postingan</label>
                                    <input type="text" name="judul" class="form-control" 
                                           value="<?= htmlspecialchars($judul) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Isi Postingan / Pengumuman</label>
                                    <textarea name="isi" class="form-control" rows="5" required><?= htmlspecialchars($isi) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=manage-kritik" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Kirim
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
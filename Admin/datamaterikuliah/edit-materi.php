<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// 1. Ambil ID materi dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID materi tidak valid!'); window.location.href='?p=materi-kuliah';</script>";
    exit;
}

// 2. Siapkan variabel untuk re-population
$jadwal_id = 0;
$judul = '';
$deskripsi = '';
$tipe_materi = 'File';
$link_url = '';
$current_file_path = ''; // Untuk menyimpan path file yang lama

// 3. Ambil data master (daftar jadwal) untuk dropdown
$jadwalList = [];
try {
    $stmt = $pdo->query("
        SELECT 
            jk.id, 
            mk.nama_mk, 
            d.nama_lengkap AS nama_dosen, 
            k.kelas, 
            k.angkatan, 
            jk.hari, 
            jk.jam_mulai
        FROM jadwal_kuliah jk
        LEFT JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN dosen d ON jk.dosen_id = d.id
        LEFT JOIN kelas k ON jk.kelas_id = k.id
        ORDER BY mk.nama_mk, k.kelas, FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
    ");
    $jadwalList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Gagal memuat daftar jadwal: " . addslashes($e->getMessage());
}

// 4. Proses Form Jika di-Submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari POST (untuk update ATAU re-populate)
    $jadwal_id = intval($_POST['jadwal_kuliah_id']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tipe_materi = trim($_POST['tipe_materi']);
    $link_url = trim($_POST['link_url']);
    $current_file_path = trim($_POST['current_file_path']); // Ambil path file lama dari hidden input

    try {
        if ($jadwal_id <= 0 || $judul === '') {
            throw new Exception("Jadwal dan Judul wajib diisi.");
        }
        
        $pdo->beginTransaction();
        
        // Siapkan variabel untuk query UPDATE
        $new_file_path = $current_file_path; // Default, tetap file lama
        $new_link_url = NULL;

        // Logika jika Tipe = Link
        if ($tipe_materi === 'Link') {
            if ($link_url === '') {
                throw new Exception("URL Link wajib diisi.");
            }
            $new_link_url = $link_url;
            $new_file_path = NULL; // Hapus path file
            
            // Hapus file fisik lama jika ada (karena tipe berubah)
            if (!empty($current_file_path) && file_exists($current_file_path)) {
                unlink($current_file_path);
            }
        
        // Logika jika Tipe = File
        } elseif ($tipe_materi === 'File') {
            $new_link_url = NULL; // Hapus link

            // Cek apakah admin mengupload file BARU
            if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
                // --- Validasi File Baru ---
                $file = $_FILES['file_materi'];
                $max_file_size = 10 * 1024 * 1024; // 10 MB
                $allowed_extensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'zip', 'rar', 'mp4', 'mkv'];
                // -------------------------

                $file_size = $file['size'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if ($file_size > $max_file_size) throw new Exception("Ukuran file baru terlalu besar (Maks 10 MB).");
                if (!in_array($file_ext, $allowed_extensions)) throw new Exception("Tipe file baru tidak diizinkan.");

                // Buat nama unik dan path
                $new_file_name = "materi_" . $jadwal_id . "_" . uniqid() . "." . $file_ext;
                $upload_path = "uploads/materi/" . $new_file_name;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Hapus file fisik LAMA (jika ada) karena diganti file baru
                    if (!empty($current_file_path) && file_exists($current_file_path)) {
                        unlink($current_file_path);
                    }
                    $new_file_path = $upload_path; // Set path baru
                } else {
                    throw new Exception("Gagal memindahkan file baru ke server.");
                }
            }
            // Jika tidak ada file baru diupload, $new_file_path tetap $current_file_path
        }
        
        // Eksekusi UPDATE
        $sql = "UPDATE materi_kuliah SET
                    jadwal_kuliah_id = ?,
                    judul = ?,
                    deskripsi = ?,
                    tipe_materi = ?,
                    file_path = ?,
                    link_url = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jadwal_id, $judul, $deskripsi, $tipe_materi, $new_file_path, $new_link_url, $id]);
        
        $pdo->commit();
        echo "<script>
                alert('✅ Materi berhasil diperbarui!'); 
                window.location.href='./?p=materi-kuliah';
              </script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = addslashes($e->getMessage());
    }
    
} else {
    // 5. Jika bukan POST (method GET), ambil data materi dari DB untuk isi form
    try {
        $stmt = $pdo->prepare("SELECT * FROM materi_kuliah WHERE id = ?");
        $stmt->execute([$id]);
        $materi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$materi) {
            echo "<script>alert('Data materi tidak ditemukan!'); window.location.href='?p=manage-materi';</script>";
            exit;
        }

        // Isi variabel dari database
        $jadwal_id = $materi['jadwal_kuliah_id'];
        $judul = $materi['judul'];
        $deskripsi = $materi['deskripsi'];
        $tipe_materi = $materi['tipe_materi'];
        $current_file_path = $materi['file_path']; // Simpan path file lama
        $link_url = $materi['link_url'];

    } catch (PDOException $e) {
        $error_message = "Gagal memuat data materi: " . addslashes($e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Edit Materi (Admin)</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=manage-materi">Manajemen Materi</a></li>
                        <li class="breadcrumb-item active">Edit Materi</li>
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
                            <h4 class="mb-0">Form Edit Materi</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="current_file_path" value="<?= htmlspecialchars($current_file_path) ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Pilih Jadwal Kuliah</label>
                                    <select name="jadwal_kuliah_id" class="form-select" required>
                                        <option value="">-- Pilih Jadwal (Matkul - Dosen - Kelas - Hari) --</option>
                                        <?php foreach ($jadwalList as $j): ?>
                                            <?php 
                                              $displayText = htmlspecialchars(
                                                  $j['nama_mk'] . ' - ' . 
                                                  $j['nama_dosen'] . ' (' . 
                                                  $j['kelas'] . ' ' . $j['angkatan'] . ') - ' . 
                                                  $j['hari'] . ' ' . substr($j['jam_mulai'], 0, 5)
                                              );
                                            ?>
                                            <option value="<?= $j['id'] ?>" <?= ($jadwal_id == $j['id']) ? 'selected' : '' ?>>
                                                <?= $displayText ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Judul Materi</label>
                                    <input type="text" name="judul" class="form-control" 
                                           value="<?= htmlspecialchars($judul) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipe Materi</label>
                                    <select name="tipe_materi" id="tipe_materi" class="form-select" required>
                                        <option value="File" <?= ($tipe_materi == 'File') ? 'selected' : '' ?>>File (Upload)</option>
                                        <option value="Link" <?= ($tipe_materi == 'Link') ? 'selected' : '' ?>>Link (Embed)</option>
                                    </select>
                                </div>

                                <div id="input_file_wrapper" class="mb-3">
                                    <label class="form-label">Upload File Baru (Opsional)</label>
                                    <input type="file" name="file_materi" class="form-control">
                                    <?php if (!empty($current_file_path) && $tipe_materi == 'File'): ?>
                                        <div class="form-text text-success">
                                            File saat ini: <strong><?= htmlspecialchars($current_file_path) ?></strong>
                                            <br>
                                            <small>Kosongkan input di atas jika tidak ingin mengganti file.</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-text">Tipe: pdf, ppt, pptx, doc, docx, zip, rar, mp4, mkv</div>
                                    <?php endif; ?>
                                </div>

                                <div id="input_link_wrapper" class="mb-3" style="display: none;">
                                    <label class="form-label">URL / Link</label>
                                    <input type="text" name="link_url" class="form-control" 
                                           value="<?= htmlspecialchars($link_url) ?>" placeholder="https://www.youtube.com/watch?v=...">
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=materi-kuliah" class="btn btn-secondary">
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

<script>
document.getElementById('tipe_materi').addEventListener('change', function() {
    if (this.value === 'File') {
        document.getElementById('input_file_wrapper').style.display = 'block';
        document.getElementById('input_link_wrapper').style.display = 'none';
    } else {
        document.getElementById('input_file_wrapper').style.display = 'none';
        document.getElementById('input_link_wrapper').style.display = 'block';
    }
});
// Trigger saat halaman load
document.getElementById('tipe_materi').dispatchEvent(new Event('change'));
</script>
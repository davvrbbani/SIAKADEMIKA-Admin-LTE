<?php
require_once "../config.php"; // Sesuaikan path

$error_message = '';
// Variabel untuk re-population
$jadwal_id = 0;
$judul = '';
$deskripsi = '';
$tipe_materi = 'File';
$link_url = '';

// Data untuk mengisi Dropdown Jadwal
$jadwalList = [];
try {
    // Query untuk mengambil semua jadwal yang ada
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

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data (untuk re-population jika error)
    $jadwal_id = intval($_POST['jadwal_kuliah_id']);
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $tipe_materi = trim($_POST['tipe_materi']);
    $link_url = trim($_POST['link_url']);

    try {
        if ($jadwal_id <= 0 || $judul === '') {
            throw new Exception("Jadwal dan Judul wajib diisi.");
        }
        
        $pdo->beginTransaction();

        // Logika jika Tipe = Link
        if ($tipe_materi === 'Link') {
            if ($link_url === '') {
                throw new Exception("URL Link wajib diisi.");
            }
            $sql = "INSERT INTO materi_kuliah (jadwal_kuliah_id, judul, deskripsi, tipe_materi, link_url, file_path) 
                    VALUES (?, ?, ?, 'Link', ?, NULL)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$jadwal_id, $judul, $deskripsi, $link_url]);
        
        // Logika jika Tipe = File
        } elseif ($tipe_materi === 'File') {
            if (!isset($_FILES['file_materi']) || $_FILES['file_materi']['error'] != 0) {
                throw new Exception("Gagal mengupload file. Pastikan file telah dipilih.");
            }

            // --- Pengaturan Batasan File ---
            $file = $_FILES['file_materi'];
            $max_file_size = 10 * 1024 * 1024; // 10 MB
            $allowed_extensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'zip', 'rar', 'mp4', 'mkv'];
            // ---------------------------------

            $file_size = $file['size'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file_size > $max_file_size) {
                throw new Exception("Ukuran file terlalu besar (Maks 10 MB).");
            }
            if (!in_array($file_ext, $allowed_extensions)) {
                throw new Exception("Tipe file tidak diizinkan. Hanya boleh: " . implode(', ', $allowed_extensions));
            }

            // Buat nama file unik dan path
            $new_file_name = "materi_" . $jadwal_id . "_" . uniqid() . "." . $file_ext;
            $upload_path = "uploads/materi/" . $new_file_name; // Pastikan folder ini ada dan writable

            if (!file_exists('uploads/materi/')) {
                mkdir('uploads/materi/', 0777, true); // Buat folder jika belum ada
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Simpan ke Database
                $sql = "INSERT INTO materi_kuliah (jadwal_kuliah_id, judul, deskripsi, tipe_materi, file_path, link_url) 
                        VALUES (?, ?, ?, 'File', ?, NULL)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$jadwal_id, $judul, $deskripsi, $upload_path]);
            } else {
                throw new Exception("Gagal memindahkan file ke server.");
            }
        }
        
        $pdo->commit();
        echo "<script>
                alert('✅ Materi berhasil ditambahkan!'); 
                window.location.href='./?p=materi-kuliah';
              </script>";
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
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Materi (Admin)</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=materi-kuliah">Manajemen Materi</a></li>
                        <li class="breadcrumb-item active">Tambah Materi</li>
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
                            <h4 class="mb-0">Form Tambah Materi</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                
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
                                           value="<?= htmlspecialchars($judul) ?>" placeholder="Contoh: Slide Pertemuan 1 - Pendahuluan" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($deskripsi) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipe Materi</label>
                                    <select name="tipe_materi" id="tipe_materi" class="form-select" required>
                                        <option value="File" <?= ($tipe_materi == 'File') ? 'selected' : '' ?>>File (Upload PDF, PPT, Video)</option>
                                        <option value="Link" <?= ($tipe_materi == 'Link') ? 'selected' : '' ?>>Link (Embed YouTube, Google Drive, dll)</option>
                                    </select>
                                </div>

                                <div id="input_file_wrapper" class="mb-3">
                                    <label class="form-label">Upload File (Maks 10 MB)</label>
                                    <input type="file" name="file_materi" class="form-control">
                                    <div class="form-text">Tipe: pdf, ppt, pptx, doc, docx, zip, rar, mp4, mkv</div>
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
// Trigger saat halaman load (jika user kembali karena error)
document.getElementById('tipe_materi').dispatchEvent(new Event('change'));
</script>
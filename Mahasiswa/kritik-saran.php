<?php
// Main config for database connection ($pdo)
require_once '../config.php';

// Include student identity logic
require_once 'config/student_identity.php';

// Pastikan user_id ada
if (!isset($_SESSION['user_id'])) {
    die("Sesi tidak valid. Silakan login ulang.");
}

$user_id = $_SESSION['user_id'];
$kelas_id_mahasiswa = $current_student['kelas_id'] ?? 0;
$error_message = '';
$success_message = '';

// --- Ambil data untuk Form (Daftar Dosen yang Mengajar Mahasiswa ini) ---
$dosenList = [];
if ($kelas_id_mahasiswa > 0) {
    try {
        // Ambil daftar dosen yang mengajar di kelas mahasiswa ini (dari jadwal)
        $dosenStmt = $pdo->prepare("
            SELECT DISTINCT d.id, d.nama_lengkap 
            FROM dosen d 
            JOIN jadwal_kuliah jk ON d.id = jk.dosen_id 
            WHERE jk.kelas_id = :kelas_id 
            ORDER BY d.nama_lengkap ASC
        ");
        $dosenStmt->execute(['kelas_id' => $kelas_id_mahasiswa]);
        $dosenList = $dosenStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Gagal memuat daftar dosen: " . $e->getMessage();
    }
}

// --- LOGIC FOR HANDLING FORM SUBMISSION (CREATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_feedback'])) {
    
    // Data umum
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $tipe_postingan = trim($_POST['tipe_postingan']);

    try {
        if (empty($judul) || empty($isi)) {
            throw new Exception("Judul dan Isi tidak boleh kosong.");
        }
        
        // ... (Logika INSERT feedback tetap sama) ...
        $parent_id = NULL;
        $is_anonim = 0;
        $target_dosen_id = NULL;
        $tipe = 'Publik';

        if ($tipe_postingan === 'Personal') {
            $tipe = 'Personal';
            $is_anonim = 1; // Wajib anonim
            $target_dosen_id = intval($_POST['target_dosen_id']);
            if ($target_dosen_id <= 0) {
                throw new Exception("Target Dosen harus dipilih.");
            }
        } else {
            $tipe = 'Publik';
            $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
            $target_dosen_id = NULL;
        }

        $sql = "INSERT INTO kritik_saran (parent_id, user_id, tipe, is_anonim, target_dosen_id, judul, isi, created_at) 
                VALUES (:parent_id, :user_id, :tipe, :is_anonim, :target_dosen_id, :judul, :isi, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'parent_id' => $parent_id,
            'user_id' => $user_id,
            'tipe' => $tipe,
            'is_anonim' => $is_anonim,
            'target_dosen_id' => $target_dosen_id,
            'judul' => $judul,
            'isi' => $isi
        ]);
        
        $success_message = "Masukan Anda telah berhasil dikirim!";

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}


// --- LOGIC FOR FETCHING DATA UNTUK TAMPILAN ---
$feedback_history = [];
$forum_terbaru = []; // <-- Variabel baru untuk widget

try {
    // 1. Ambil Riwayat Postingan Saya (Query lama, tetap)
    $stmt_history = $pdo->prepare(
        "SELECT * FROM kritik_saran 
         WHERE user_id = :user_id AND parent_id IS NULL 
         ORDER BY created_at DESC"
    );
    $stmt_history->execute(['user_id' => $user_id]);
    $feedback_history = $stmt_history->fetchAll();

    // === TAMBAHAN BARU: Ambil Postingan Forum Publik Terbaru ===
    // (Query ini tidak pakai 'WHERE user_id' karena ini postingan publik)
    $stmt_forum = $pdo->query(
        "SELECT id, judul, created_at 
         FROM kritik_saran 
         WHERE tipe = 'Publik' AND parent_id IS NULL 
         ORDER BY created_at DESC 
         LIMIT 5" // Ambil 5 terbaru
    );
    $forum_terbaru = $stmt_forum->fetchAll(PDO::FETCH_ASSOC);
    // === END TAMBAHAN BARU ===

} catch (PDOException $e) {
    // Jika gagal ambil data, tampilkan pesan tapi jangan hentikan skrip
    $error_message = "Gagal mengambil data riwayat/forum: " . $e->getMessage();
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Forum & Kritik Saran</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kritik dan Saran</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> <?= htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="publik-tab" data-bs-toggle="tab" data-bs-target="#publik" type="button" role="tab" aria-controls="publik" aria-selected="true">
                                        <i class="fas fa-bullhorn me-2"></i> Forum Publik
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="false">
                                        <i class="fas fa-user-secret me-2"></i> Kritik Personal (Dosen)
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="publik" role="tabpanel" aria-labelledby="publik-tab">
                                    <p class="text-muted">Postingan ini dapat dilihat oleh mahasiswa dan dosen lain.</p>
                                    <form action="./?p=kritik-saran" method="POST">
                                        <input type="hidden" name="tipe_postingan" value="Publik">
                                        <div class="mb-3">
                                            <label for="judul_publik" class="form-label">Judul Postingan</label>
                                            <input type="text" class="form-control" id="judul_publik" name="judul" placeholder="Kritik/Saran tentang Fasilitas, Kampus, dll" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="isi_publik" class="form-label">Isi Pesan</label>
                                            <textarea class="form-control" id="isi_publik" name="isi" rows="5" required></textarea>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" id="is_anonim" name="is_anonim">
                                            <label class="form-check-label" for="is_anonim">
                                                Kirim sebagai Anonim (Sembunyikan nama saya)
                                            </label>
                                        </div>
                                        <button type="submit" name="kirim_feedback" class="btn btn-primary float-end">
                                            <i class="bi bi-send"></i> Kirim ke Forum
                                        </button>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        <strong>Wajib Anonim.</strong> Masukan ini bersifat personal dan privat. Nama Anda tidak akan ditampilkan kepada Dosen.
                                    </div>
                                    <form action="./?p=kritik-saran" method="POST">
                                        <input type="hidden" name="tipe_postingan" value="Personal">
                                        <div class="mb-3">
                                            <label for="target_dosen_id" class="form-label">Target Dosen</label>
                                            <select class="form-select" id="target_dosen_id" name="target_dosen_id" required>
                                                <option value="">-- Pilih Dosen yang Anda Temui di Kelas --</option>
                                                <?php foreach ($dosenList as $dosen): ?>
                                                    <option value="<?= $dosen['id']; ?>">
                                                        <?= htmlspecialchars($dosen['nama_lengkap']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (empty($dosenList)): ?>
                                                     <option value="" disabled>Anda belum terdaftar di kelas/jadwal manapun.</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="judul_personal" class="form-label">Judul Masukan</label>
                                            <input type="text" class="form-control" id="judul_personal" name="judul" placeholder="Kritik/Saran tentang metode mengajar, dll" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="isi_personal" class="form-label">Isi Pesan</label>
                                            <textarea class="form-control" id="isi_personal" name="isi" rows="5" required></textarea>
                                        </div>
                                        <button type="submit" name="kirim_feedback" class="btn btn-primary float-end">
                                            <i class="bi bi-send"></i> Kirim Personal
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Postingan Saya (Utama)</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($feedback_history)): ?>
                                <div class="alert alert-light text-center m-3">
                                    Anda belum pernah mengirimkan kritik atau saran.
                                </div>
                            <?php else: ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Judul Postingan</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Tanggal</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feedback_history as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['judul']); ?></strong>
                                                    <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars(substr($item['isi'], 0, 100))); ?>...</p>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($item['tipe'] == 'Publik'): ?>
                                                        <span class="badge bg-success">Publik</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Personal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($item['is_anonim'] == 1): ?>
                                                        <span class="badge bg-secondary">Anonim</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Publik</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle small"><?= date('d M Y', strtotime($item['created_at'])); ?></td>
                                                <td class="text-center align-middle">
                                                    <a href="./?p=lihat-postingan&id=<?= $item['id']; ?>" class="btn btn-sm btn-primary">
                                                        Lihat
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mt-4"> <div class="card-header bg-light">
                            <h3 class="card-title">Forum Publik Terbaru</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($forum_terbaru)): ?>
                                <p class="text-muted">Belum ada postingan di forum publik.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($forum_terbaru as $post): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <a href="./?p=lihat-postingan&id=<?= $post['id']; ?>" class="fw-bold text-decoration-none">
                                                <?= htmlspecialchars($post['judul']); ?>
                                            </a>
                                            <small class="d-block text-muted">
                                                <?= date('d M Y', strtotime($post['created_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="./?p=lihat-postingan&id=<?= $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Lihat
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-center">
                            <a href="./?p=lihat-postingan" class="btn btn-primary">
                                <i class="fas fa-comments me-2"></i> Lihat Semua Forum
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</main>
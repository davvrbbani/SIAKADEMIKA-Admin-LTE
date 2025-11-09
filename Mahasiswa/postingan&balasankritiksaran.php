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
$error_message = '';
$success_message = '';

// Ambil ID postingan utama dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID postingan tidak valid.</div>";
    exit;
}

// --- LOGIKA UNTUK MENGIRIM BALASAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_balasan'])) {
    $parent_id = intval($_POST['parent_id']);
    $isi = trim($_POST['isi']);
    $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
    
    try {
        if (empty($isi)) {
            throw new Exception("Balasan tidak boleh kosong.");
        }
        if ($parent_id !== $id) {
            throw new Exception("ID postingan tidak cocok.");
        }

        // Simpan balasan ke database
        $sql = "INSERT INTO kritik_saran (parent_id, user_id, tipe, is_anonim, target_dosen_id, judul, isi, created_at) 
                VALUES (:parent_id, :user_id, 'Publik', :is_anonim, NULL, NULL, :isi, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'parent_id' => $parent_id,
            'user_id' => $user_id,
            'is_anonim' => $is_anonim,
            'isi' => $isi
        ]);
        
        // Redirect ke halaman yang sama untuk refresh
        echo "<script>window.location.href = './?p=forum-balasan&id=$id&status=reply_success';</script>";
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Ambil pesan sukses (jika ada)
if (isset($_GET['status']) && $_GET['status'] == 'reply_success') {
    $success_message = "Balasan Anda berhasil terkirim!";
}

// Fungsi helper untuk nama & anonim (WAJIB ADA)
function getAuthorDisplay($post) {
    if ($post['is_anonim'] == 1) {
        if (!empty($post['author_dosen'])) return 'Dosen (Anonim)';
        if (!empty($post['author_mahasiswa'])) return 'Mahasiswa (Anonim)';
        return 'Anonim';
    }
    $nama = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'User';
    return htmlspecialchars($nama);
}

// --- Ambil Data Postingan & Balasannya ---
try {
    // Fungsi untuk join data penulis
    $join_sql = "
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d ON u.id = d.user_id
    ";

    // 1. Ambil data Postingan Utama
    $stmtMain = $pdo->prepare("SELECT ks.*, u.username AS author_username, m.nama_lengkap AS author_mahasiswa, d.nama_lengkap AS author_dosen $join_sql WHERE ks.id = ? AND ks.tipe = 'Publik' AND ks.parent_id IS NULL");
    $stmtMain->execute([$id]);
    $mainPost = $stmtMain->fetch(PDO::FETCH_ASSOC);

    if (!$mainPost) {
        echo "<div class='alert alert-warning'>Postingan tidak ditemukan atau bukan postingan publik.</div>";
        exit;
    }

    // 2. Ambil semua Balasannya
    $stmtReplies = $pdo->prepare("SELECT ks.*, u.username AS author_username, m.nama_lengkap AS author_mahasiswa, d.nama_lengkap AS author_dosen $join_sql WHERE ks.parent_id = ? ORDER BY ks.created_at ASC");
    $stmtReplies->execute([$id]);
    $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: Gagal mengambil data postingan: " . $e->getMessage());
}
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Lihat Postingan</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <!-- Link ini diperbaiki agar kembali ke halaman list postinganmu -->
                        <li class="breadcrumb-item"><a href="./?p=lihat-postingan">Forum Publik</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lihat Postingan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    
                    <!-- Pesan Sukses atau Error -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?= $success_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?= $error_message; ?></div>
                    <?php endif; ?>

                    <!-- Tombol Kembali (diperbaiki ke halaman list) -->
                    <div class="mb-3">
                        <a href="./?p=lihat-postingan" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Forum
                        </a>
                    </div>
                    
                    <!-- Card Postingan Utama -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0">
                                <?= htmlspecialchars($mainPost['judul']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Tampilkan isi lengkap -->
                            <p class="card-text fs-5"><?= nl2br(htmlspecialchars($mainPost['isi'])); ?></p>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between">
                            <span>Diposting oleh: <strong><?= getAuthorDisplay($mainPost); ?></strong></span>
                            <span><?= htmlspecialchars(date('d F Y, H:i', strtotime($mainPost['created_at']))); ?></span>
                        </div>
                    </div>

                    <hr>

                    <!-- Daftar Balasan -->
                    <h4 class="mb-3">Balasan (<?= count($replies); ?>)</h4>
                    <?php if (empty($replies)): ?>
                        <div class="alert alert-info">Belum ada balasan untuk postingan ini.</div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <div class="card shadow-sm mb-3">
                                <div class="card-body">
                                    <p class="card-text"><?= nl2br(htmlspecialchars($reply['isi'])); ?></p>
                                </div>
                                <div class="card-footer text-muted d-flex justify-content-between">
                                    <span>Oleh: <strong><?= getAuthorDisplay($reply); ?></strong></span>
                                    <small><?= htmlspecialchars(date('d M Y, H:i', strtotime($reply['created_at']))); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <hr>

                    <!-- Form Kirim Balasan -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Kirim Balasan Anda</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="parent_id" value="<?= $mainPost['id']; ?>">
                                <div class="mb-3">
                                    <label for="isi_balasan" class="form-label">Isi Balasan:</label>
                                    <textarea name="isi" id="isi_balasan" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_anonim_reply" name="is_anonim">
                                    <label class="form-check-label" for="is_anonim_reply">
                                        Kirim sebagai Anonim
                                    </label>
                                </div>
                                <button type="submit" name="kirim_balasan" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Kirim Balasan
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->
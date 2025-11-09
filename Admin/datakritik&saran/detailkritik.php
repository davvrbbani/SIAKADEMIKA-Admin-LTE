<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID postingan tidak valid!'); window.location.href='?p=manage-kritik';</script>";
    exit;
}

// Fungsi helper untuk mengambil data user (DRY - Don't Repeat Yourself)
function getPostDetails($pdo, $postId) {
    $stmt = $pdo->prepare("
        SELECT 
            ks.*,
            u.username AS author_username,
            m.nama_lengkap AS author_mahasiswa,
            d.nama_lengkap AS author_dosen
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d ON u.id = d.user_id
        WHERE ks.id = ?
        LIMIT 1
    ");
    $stmt->execute([$postId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi helper untuk mengambil NAMA pengirim
function getAuthorName($post) {
    $nama = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'N/A';
    if ($post['is_anonim'] == 1) {
        // Admin bisa lihat nama asli + status anonim
        return htmlspecialchars($nama) . ' <span class="badge bg-secondary">Anonim</span>';
    }
    return htmlspecialchars($nama);
}

try {
    // 1. Ambil data Postingan Utama
    $mainPost = getPostDetails($pdo, $id);
    if (!$mainPost) {
        echo "<div class='alert alert-warning'>Data postingan tidak ditemukan.</div>";
        exit;
    }

    // 2. Ambil semua Balasannya
    $replyStmt = $pdo->prepare("
        SELECT 
            ks.*,
            u.username AS author_username,
            m.nama_lengkap AS author_mahasiswa,
            d.nama_lengkap AS author_dosen
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d ON u.id = d.user_id
        WHERE ks.parent_id = ?
        ORDER BY ks.created_at ASC
    ");
    $replyStmt->execute([$id]);
    $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Query error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Detail Postingan</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=manage-kritik">Forum & Kritik</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
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
                <div class="col-12">
                    <!-- Tombol Aksi Utama -->
                    <div class="mb-3">
                        <a href="./?p=manage-kritik" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                        <a href="./?p=edit-kritik-admin&id=<?= $mainPost['id']; ?>" class="btn btn-warning text-white">
                            <i class="fas fa-edit"></i> Edit Postingan Utama
                        </a>
                        <a href='./?p=hapus-kritik&id=<?= $mainPost["id"]; ?>' 
                           class='btn btn-danger' 
                           onclick="return confirm('⚠️ Yakin mau hapus postingan ini? SEMUA BALASAN akan ikut terhapus.');">
                            <i class='fas fa-trash'></i> Hapus Thread
                        </a>
                    </div>

                    <!-- Card Postingan Utama -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between">
                            <h4 class="mb-0"><?= htmlspecialchars($mainPost['judul'] ?? 'Detail Postingan'); ?></h4>
                            <span>Tipe: <?= htmlspecialchars($mainPost['tipe']); ?></span>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?= nl2br(htmlspecialchars($mainPost['isi'])); ?></p>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between">
                            <span>Di-post oleh: <?= getAuthorName($mainPost); ?></span>
                            <span><?= htmlspecialchars(date('d F Y, H:i', strtotime($mainPost['created_at']))); ?></span>
                        </div>
                    </div>

                    <!-- Daftar Balasan -->
                    <h4 class="mb-3">Balasan (<?= count($replies); ?>)</h4>
                    <?php if (empty($replies)): ?>
                        <div class="alert alert-info">Belum ada balasan.</div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <div class="card shadow-sm mb-3">
                                <div class="card-body">
                                    <p class="card-text"><?= nl2br(htmlspecialchars($reply['isi'])); ?></p>
                                </div>
                                <div class="card-footer text-muted d-flex justify-content-between">
                                    <span>Oleh: <?= getAuthorName($reply); ?></span>
                                    <span>
                                        <?= htmlspecialchars(date('d M Y, H:i', strtotime($reply['created_at']))); ?>
                                        <!-- Hapus balasan individual -->
                                        <a href='./?p=hapus-kritik&id=<?= $reply["id"]; ?>' 
                                           class='btn btn-danger btn-xs ms-2' 
                                           onclick="return confirm('Yakin mau hapus balasan ini?');">Hapus
                                            <i class='fas fa-trash'></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- (Opsional) Form Balas untuk Admin -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Beri Balasan (Sebagai Admin)</h5>
                        </div>
                        <div class="card-body">
                            <!-- Nanti form ini akan submit ke 'add-kritik-admin.php' tapi dengan parent_id -->
                            <form action="./?p=add-kritik-admin" method="POST">
                                <input type="hidden" name="parent_id" value="<?= $mainPost['id']; ?>">
                                <input type="hidden" name="tipe" value="<?= $mainPost['tipe']; ?>">
                                <input type="hidden" name="judul" value="Balasan untuk: <?= htmlspecialchars($mainPost['judul'] ?? $mainPost['id']); ?>">
                                <input type="hidden" name="is_anonim" value="0"> <!-- Admin tidak anonim -->
                                
                                <div class="mb-3">
                                    <label class="form-label">Isi Balasan:</label>
                                    <textarea name="isi" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
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
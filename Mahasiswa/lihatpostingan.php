<?php
// Main config for database connection ($pdo)
require_once '../config.php';
// Include student identity logic
require_once 'config/student_identity.php';

// Fungsi helper untuk menentukan nama penulis & status anonim
function getAuthorDisplay($post) {
    if ($post['is_anonim'] == 1) {
        // Jika anonim, tampilkan status (Mahasiswa/Dosen) jika bisa
        if (!empty($post['author_dosen'])) {
            return 'Dosen (Anonim)';
        } elseif (!empty($post['author_mahasiswa'])) {
            return 'Mahasiswa (Anonim)';
        }
        return 'Anonim';
    }
    
    // Jika tidak anonim, tampilkan nama asli
    $nama = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'User';
    return htmlspecialchars($nama);
}

// --- Ambil semua postingan 'Publik' (Top-Level) ---
$forum_posts = [];
try {
    // Query ini mengambil semua postingan, nama penulis, dan JUMLAH BALASAN
    $stmt = $pdo->query("
        SELECT 
            ks.id, ks.judul, ks.isi, ks.created_at, ks.is_anonim,
            u.username AS author_username,
            m.nama_lengkap AS author_mahasiswa,
            d.nama_lengkap AS author_dosen,
            (SELECT COUNT(*) FROM kritik_saran replies WHERE replies.parent_id = ks.id) AS total_balasan
        FROM 
            kritik_saran AS ks
        LEFT JOIN 
            users AS u ON ks.user_id = u.id
        LEFT JOIN 
            mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN 
            dosen AS d ON u.id = d.user_id
        WHERE 
            ks.tipe = 'Publik' AND ks.parent_id IS NULL
        ORDER BY
            ks.created_at DESC
    ");
    $forum_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: Gagal mengambil data forum: " . $e->getMessage());
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Forum Publik</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Forum Publik</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="mb-3">
                        <a href="./?p=kritik-saran" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i> Kembali
                        </a>
                    </div>
                    <?php if (empty($forum_posts)): ?>
                        <div class="alert alert-info text-center">
                            Belum ada postingan di forum publik. Jadilah yang pertama!
                        </div>
                    <?php else: ?>
                        <?php foreach ($forum_posts as $post): ?>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <?= htmlspecialchars($post['judul']); ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?= nl2br(htmlspecialchars(substr($post['isi'], 0, 250))); ?>
                                        <?php if (strlen($post['isi']) > 250) echo '...'; ?>
                                    </p>
                                </div>
                                <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                                    <div>
                                        Diposting oleh: <strong><?= getAuthorDisplay($post); ?></strong>
                                        <br>
                                        <small><?= date('d F Y, H:i', strtotime($post['created_at'])); ?></small>
                                    </div>
                                    <a href="./?p=forum-balasan&id=<?= $post['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Lihat Balasan (<?= $post['total_balasan']; ?>)
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </main>
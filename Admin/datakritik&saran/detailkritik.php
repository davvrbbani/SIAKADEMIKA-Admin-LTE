<?php
require_once "../config.php"; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo "<script>window.location.href='?p=manage-kritik';</script>"; exit; }

function getPostDetails($pdo, $postId) {
    $stmt = $pdo->prepare("
        SELECT ks.*, u.username AS author_username, m.nama_lengkap AS author_mahasiswa, d.nama_lengkap AS author_dosen
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d ON u.id = d.user_id
        WHERE ks.id = ? LIMIT 1
    ");
    $stmt->execute([$postId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAuthorName($post) {
    $nama = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'N/A';
    if ($post['is_anonim'] == 1) return htmlspecialchars($nama) . ' <span class="badge bg-secondary">Anonim</span>';
    return htmlspecialchars($nama);
}

try {
    $mainPost = getPostDetails($pdo, $id);
    if (!$mainPost) { echo "<div class='alert alert-warning'>Data tidak ditemukan.</div>"; exit; }

    $replyStmt = $pdo->prepare("
        SELECT ks.*, u.username AS author_username, m.nama_lengkap AS author_mahasiswa, d.nama_lengkap AS author_dosen
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d ON u.id = d.user_id
        WHERE ks.parent_id = ? ORDER BY ks.created_at ASC
    ");
    $replyStmt->execute([$id]);
    $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { echo $e->getMessage(); exit; }
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3>Detail Postingan</h3></div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="./?p=manage-kritik" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="./?p=edit-kritik-admin&id=<?= $mainPost['id']; ?>" class="btn btn-warning text-white"><i class="fas fa-edit"></i> Edit</a>
                <a href='./?p=hapus-kritik&id=<?= $mainPost["id"]; ?>' class='btn btn-danger' onclick="return confirm('Hapus thread ini?');"><i class='fas fa-trash'></i> Hapus</a>
            </div>

            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <span><?= htmlspecialchars($mainPost['tipe']); ?></span>
                </div>
                <div class="card-body">
                    <h5 class="mb-0">Judul Postingan: <?= htmlspecialchars($mainPost['judul'] ?? 'Detail Postingan'); ?></h5>
                <p class="card-text" style="font-size: 1.1rem;"> Keterangan: <?= nl2br(htmlspecialchars($mainPost['isi'])); ?></p>
                    
                    <?php if (!empty($mainPost['foto_lampiran']) && file_exists("../" . $mainPost['foto_lampiran'])): ?>
                        <div class="mt-3">
                            <img src="../<?= htmlspecialchars($mainPost['foto_lampiran']) ?>" class="img-fluid rounded border" style="max-height: 400px;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted d-flex justify-content-between">
                    <span>Pengirim: <?= getAuthorName($mainPost); ?></span>
                    <span><?= htmlspecialchars(date('d F Y, H:i', strtotime($mainPost['created_at']))); ?></span>
                </div>
            </div>

            <h5 class="mb-3 border-bottom pb-2">Balasan (<?= count($replies); ?>)</h5>
            <?php foreach ($replies as $reply): ?>
                <div class="card shadow-sm mb-3 ms-4 border-start border-3 border-secondary">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between mb-2">
                            <strong><?= getAuthorName($reply); ?></strong>
                            <small class="text-muted"><?= htmlspecialchars(date('d M Y, H:i', strtotime($reply['created_at']))); ?></small>
                        </div>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($reply['isi'])); ?></p>

                        <?php if (!empty($reply['foto_lampiran']) && file_exists("../" . $reply['foto_lampiran'])): ?>
                            <div class="mt-2">
                                <img src="../<?= htmlspecialchars($reply['foto_lampiran']) ?>" class="img-fluid rounded border" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer py-1 bg-light text-end">
                        <a href='./?p=hapus-kritik&id=<?= $reply["id"]; ?>' class='btn btn-danger btn-sm py-0' onclick="return confirm('Hapus balasan ini?');">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light"><strong>Beri Balasan</strong></div>
                <div class="card-body">
                    <form action="./?p=add-kritik-admin" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="parent_id" value="<?= $mainPost['id']; ?>">
                        <input type="hidden" name="tipe" value="<?= $mainPost['tipe']; ?>">
                        <input type="hidden" name="judul" value="Balasan: <?= htmlspecialchars($mainPost['judul']); ?>">
                        
                        <div class="mb-2">
                            <textarea name="isi" class="form-control" rows="3" placeholder="Tulis balasan..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <input type="file" name="foto_lampiran" class="form-control form-control-sm w-50">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Kirim Balasan</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>
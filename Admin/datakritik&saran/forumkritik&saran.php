<?php
require_once "../config.php"; 

function getAuthorNameForAdmin($post) {
    $nama = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'N/A';
    $display = htmlspecialchars($nama);
    if ($post['is_anonim'] == 1) $display .= ' <span class="badge bg-secondary ms-1">Anonim</span>';
    return $display;
}

try {
    // Tambahkan 'foto_lampiran' di SELECT
    $stmt = $pdo->query("
        SELECT 
            ks.id, ks.judul, ks.isi, ks.tipe, ks.is_anonim, ks.created_at, ks.foto_lampiran,
            u.username AS author_username, m.nama_lengkap AS author_mahasiswa,
            d_author.nama_lengkap AS author_dosen, d_target.nama_lengkap AS target_dosen_nama
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d_author ON u.id = d_author.user_id
        LEFT JOIN dosen AS d_target ON ks.target_dosen_id = d_target.id
        WHERE ks.parent_id IS NULL
        ORDER BY ks.created_at DESC
    ");
    $postRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { echo "Error: " . $e->getMessage(); $postRows = []; }
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3>Manajemen Forum</h3></div>
                <div class="col-sm-6 text-end">
                    <a href="./?p=add-kritik-admin" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Buat Postingan</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <?php if (empty($postRows)): ?>
                <div class="alert alert-info text-center">Belum ada data postingan.</div>
            <?php else: ?>
                <?php foreach ($postRows as $p): ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($p['judul'] ?? 'Tanpa Judul'); ?></h5>
                            <span class="badge <?= $p['tipe'] == 'Publik' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $p['tipe'] ?></span>
                        </div>
                        <div class="card-body">
                            <p class="card-text fst-italic">"<?= nl2br(htmlspecialchars(substr($p['isi'], 0, 200))); ?>..."</p>
                            
                            <?php if (!empty($p['foto_lampiran']) && file_exists("../" . $p['foto_lampiran'])): ?>
                                <div class="mt-2">
                                    <img src="../<?= htmlspecialchars($p['foto_lampiran']) ?>" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                </div>
                            <?php endif; ?>

                            <?php if ($p['tipe'] == 'Personal'): ?>
                                <p class="mb-0 mt-2"><strong>Target:</strong> <?= htmlspecialchars($p['target_dosen_nama'] ?? 'N/A'); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Pengirim:</strong> <?= getAuthorNameForAdmin($p); ?><br>
                                <small><?= htmlspecialchars(date('d M Y, H:i', strtotime($p['created_at']))); ?></small>
                            </div>
                            <div class='btn-group btn-group-sm'>
                                <a href='./?p=detail-kritik-admin&id=<?= $p["id"]; ?>' class='btn btn-info text-white'><i class='fas fa-eye'></i> Detail</a>
                                <a href='./?p=edit-kritik-admin&id=<?= $p["id"]; ?>' class='btn btn-warning text-white'><i class='fas fa-edit'></i> Edit</a>
                                <a href='./?p=hapus-kritik&id=<?= $p["id"]; ?>' class='btn btn-danger' onclick="return confirm('Hapus thread ini?');"><i class='fas fa-trash'></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
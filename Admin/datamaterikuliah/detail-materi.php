<?php
require_once "../config.php"; // Sesuaikan path

// Ambil id materi dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID materi tidak valid.</div>";
    exit;
}

try {
    // Query JOIN lengkap untuk 1 item materi
    $stmt = $pdo->prepare("
        SELECT 
            m.*, -- Ambil semua data dari tabel materi_kuliah
            jk.hari, 
            jk.jam_mulai, 
            jk.jam_selesai, 
            jk.ruangan,
            mk.nama_mk,
            mk.kode_mk,
            d.nama_lengkap AS nama_dosen,
            k.kelas, 
            k.angkatan
        FROM 
            materi_kuliah AS m
        LEFT JOIN 
            jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        LEFT JOIN 
            kelas AS k ON jk.kelas_id = k.id
        WHERE 
            m.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $materi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$materi) {
        echo "<div class='alert alert-warning'>Data materi tidak ditemukan.</div>";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Query error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Detail Materi Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=materi-kuliah">Manajemen Materi</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Detail Materi</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Judul Materi</th>
                                    <td><?= htmlspecialchars($materi['judul']) ?></td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td><?= nl2br(htmlspecialchars($materi['deskripsi'])) // nl2br untuk hargai enter ?></td>
                                </tr>
                                <tr>
                                    <th>Tipe Materi</th>
                                    <td>
                                        <?php if ($materi['tipe_materi'] == 'File'): ?>
                                            <span class="badge bg-primary">File</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Link</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>File / Link</th>
                                    <td>
                                        <?php if ($materi['tipe_materi'] == 'File' && !empty($materi['file_path'])): ?>
                                            <?php
                                                // Asumsi $materi['file_path'] = "uploads/materi/namafile.pdf"
                                                // Dan file ini ada di dalam folder 'Admin'
                                                $filePath = htmlspecialchars($materi['file_path']);
                                                if (file_exists($filePath)) {
                                                    echo "<a href='{$filePath}' target='_blank' class='btn btn-sm btn-success'>
                                                            <i class='fas fa-download'></i> Download File
                                                          </a>";
                                                    echo " <small class='text-muted'>({$filePath})</small>";
                                                } else {
                                                    echo "<span class='text-danger'>File tidak ditemukan di server: {$filePath}</span>";
                                                }
                                            ?>
                                        <?php elseif ($materi['tipe_materi'] == 'Link' && !empty($materi['link_url'])): ?>
                                            <?php $link = htmlspecialchars($materi['link_url']); ?>
                                            <a href="<?= $link ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-external-link-alt"></i> Buka Tautan
                                            </a>
                                            <br>
                                            <small><a href="<?= $link ?>" target="_blank"><?= $link ?></a></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <tr><td colspan="2" class="bg-light"><strong>Detail Jadwal Terkait</strong></td></tr>

                                <tr>
                                    <th>Mata Kuliah</th>
                                    <td><?= htmlspecialchars($materi['nama_mk'] ?? 'N/A') . ' (' . htmlspecialchars($materi['kode_mk'] ?? 'N/A') . ')' ?></td>
                                </tr>
                                <tr>
                                    <th>Dosen Pengampu</th>
                                    <td><?= htmlspecialchars($materi['nama_dosen'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td><?= htmlspecialchars($materi['kelas'] ?? 'N/A') . ' (Angk. ' . htmlspecialchars($materi['angkatan'] ?? '-') . ')' ?></td>
                                </tr>
                                 <tr>
                                    <th>Jadwal Hari</th>
                                    <td><?= htmlspecialchars($materi['hari'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Jadwal Waktu</th>
                                    <td><?= htmlspecialchars(substr($materi['jam_mulai'] ?? '00:00', 0, 5) . ' - ' . substr($materi['jam_selesai'] ?? '00:00', 0, 5)) ?></td>
                                </tr>
                                <tr>
                                    <th>Ruangan</th>
                                    <td><?= htmlspecialchars($materi['ruangan'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Di-upload pada</th>
                                    <td><?= htmlspecialchars($materi['created_at']) ?></td>
                                </tr>
                            </table>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="./?p=materi-kuliah" class="btn btn-primary text-white">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="./?p=edit-materi&id=<?= $materi['id'] ?>" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="./?p=hapus-materi&id=<?= $materi['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Yakin mau hapus materi ini? File fisik (jika ada) juga akan terhapus.');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
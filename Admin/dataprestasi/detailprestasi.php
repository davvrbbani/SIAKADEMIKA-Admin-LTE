<?php
require_once "../config.php"; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo "<div class='alert alert-danger'>ID tidak valid.</div>"; exit; }

$stmt = $pdo->prepare("
    SELECT p.*, m.nama_lengkap AS nama_mhs, m.nim, u.username AS penginput
    FROM prestasi_mahasiswa p
    LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id
    LEFT JOIN users u ON p.input_by_user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$prestasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestasi) { echo "<div class='alert alert-warning'>Data tidak ditemukan.</div>"; exit; }
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3>Detail Prestasi</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">Informasi</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th width="35%">Mahasiswa</th><td>: <?= htmlspecialchars($prestasi['nama_mhs']) ?></td></tr>
                                <tr><th>NIM</th><td>: <?= htmlspecialchars($prestasi['nim']) ?></td></tr>
                                <tr><th>Judul</th><td>: <strong><?= htmlspecialchars($prestasi['judul_prestasi']) ?></strong></td></tr>
                                <tr><th>Tingkat</th><td>: <span class="badge bg-info"><?= htmlspecialchars($prestasi['tingkat']) ?></span></td></tr>
                                <tr><th>Tanggal</th><td>: <?= date('d F Y', strtotime($prestasi['tanggal_diraih'])) ?></td></tr>
                                <tr><th>Deskripsi</th><td>: <?= nl2br(htmlspecialchars($prestasi['deskripsi'])) ?></td></tr>
                                <tr><th>Penginput</th><td>: <?= htmlspecialchars($prestasi['penginput']) ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">Bukti / Sertifikat</div>
                        <div class="card-body text-center bg-light" style="min-height: 300px; display:flex; align-items:center; justify-content:center;">
                            
                            <?php if (!empty($prestasi['foto_bukti']) && file_exists("../" . $prestasi['foto_bukti'])): ?>
                                <?php 
                                    $ext = strtolower(pathinfo($prestasi['foto_bukti'], PATHINFO_EXTENSION));
                                    $fileUrl = "../" . htmlspecialchars($prestasi['foto_bukti']);
                                ?>

                                <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                    <div>
                                        <img src="<?= $fileUrl ?>" class="img-fluid rounded border shadow-sm mb-3" style="max-height: 400px;">
                                        <br>
                                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fas fa-search-plus"></i> Lihat Ukuran Penuh
                                        </a>
                                    </div>

                                <?php elseif ($ext === 'pdf'): ?>
                                    <div class="w-100">
                                        <iframe src="<?= $fileUrl ?>" width="100%" height="400px" style="border:none;"></iframe>
                                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-file-pdf"></i> Buka PDF
                                        </a>
                                    </div>

                                <?php else: ?>
                                    <a href="<?= $fileUrl ?>" class="btn btn-warning">
                                        <i class="fas fa-download"></i> Download File (<?= $ext ?>)
                                    </a>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i><br>
                                    Tidak ada bukti foto/file yang diupload.
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-start mt-3">
                <a href="?p=manage-prestasi" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="?p=edit-prestasi&id=<?= $id ?>" class="btn btn-warning text-white me-2"><i class="fas fa-edit"></i> Edit</a>
                <a href="?p=hapus-prestasi&id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i> Hapus</a>
            </div>
        </div>
    </div>
</main>
<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID prestasi tidak valid.</div>";
    exit;
}

try {
    // Query JOIN lengkap untuk 1 item
    $stmt = $pdo->prepare("
        SELECT 
            p.*, -- Ambil semua data dari prestasi
            m.nama_lengkap AS nama_mahasiswa,
            m.nim,
            u.username AS nama_penginput
        FROM 
            prestasi_mahasiswa AS p
        LEFT JOIN 
            mahasiswa AS m ON p.mahasiswa_id = m.id
        LEFT JOIN 
            users AS u ON p.input_by_user_id = u.id
        WHERE 
            p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $prestasi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prestasi) {
        echo "<div class='alert alert-warning'>Data prestasi tidak ditemukan.</div>";
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
                <div class="col-sm-6"><h3 class="mb-0">Detail Prestasi</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=manage-prestasi">Manajemen Prestasi</a></li>
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
                            <h4 class="mb-0">Detail Prestasi Mahasiswa</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Mahasiswa</th>
                                    <td><?= htmlspecialchars($prestasi['nama_mahasiswa'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td><?= htmlspecialchars($prestasi['nim'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Judul Prestasi</th>
                                    <td><?= htmlspecialchars($prestasi['judul_prestasi']) ?></td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td><?= nl2br(htmlspecialchars($prestasi['deskripsi'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Tingkat</th>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($prestasi['tingkat']) ?></span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Diraih</th>
                                    <td><?= htmlspecialchars(date('d F Y', strtotime($prestasi['tanggal_diraih']))) ?></td>
                                </tr>
                                <tr>
                                    <th>Diinput oleh</th>
                                    <td><?= htmlspecialchars($prestasi['nama_penginput'] ?? 'N/A') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer text-end">
                            <a href="./?p=manage-prestasi" class="btn btn-primary text-white">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="./?p=edit-prestasi&id=<?= $prestasi['id'] ?>" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="./?p=hapus-prestasi&id=<?= $prestasi['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Yakin mau hapus prestasi ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
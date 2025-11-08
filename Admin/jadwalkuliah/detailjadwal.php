<?php
require_once "../config.php"; // Sesuaikan path

// Ambil id jadwal dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID jadwal tidak valid.</div>";
    exit;
}

try {
    // Query JOIN lengkap untuk 1 item
    $stmt = $pdo->prepare("
        SELECT 
            jk.id, 
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
            jadwal_kuliah AS jk
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        LEFT JOIN 
            kelas AS k ON jk.kelas_id = k.id
        WHERE 
            jk.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$jadwal) {
        echo "<div class='alert alert-warning'>Data jadwal tidak ditemukan.</div>";
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
                <div class="col-sm-6"><h3 class="mb-0">Detail Jadwal Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=jadwal-kuliah">Jadwal Kuliah</a></li>
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
                            <h4 class="mb-0">Detail Jadwal</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Mata Kuliah</th>
                                    <td><?= htmlspecialchars($jadwal['nama_mk'] ?? 'N/A') . ' (' . htmlspecialchars($jadwal['kode_mk'] ?? 'N/A') . ')' ?></td>
                                </tr>
                                <tr>
                                    <th>Dosen Pengampu</th>
                                    <td><?= htmlspecialchars($jadwal['nama_dosen'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td><?= htmlspecialchars($jadwal['kelas'] ?? 'N/A') . ' (Angk. ' . htmlspecialchars($jadwal['angkatan'] ?? '-') . ')' ?></td>
                                </tr>
                                 <tr>
                                    <th>Hari</th>
                                    <td><?= htmlspecialchars($jadwal['hari']) ?></td>
                                </tr>
                                <tr>
                                    <th>Waktu</th>
                                    <td><?= htmlspecialchars(substr($jadwal['jam_mulai'], 0, 5) . ' - ' . substr($jadwal['jam_selesai'], 0, 5)) ?></td>
                                </tr>
                                <tr>
                                    <th>Ruangan</th>
                                    <td><?= htmlspecialchars($jadwal['ruangan']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer text-end">
                            <a href="./?p=jadwal-kuliah" class="btn btn-primary text-white">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="./?p=edit-jadwal&id=<?= $jadwal['id'] ?>" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="./?p=hapus-jadwal&id=<?= $jadwal['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Yakin mau hapus jadwal ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
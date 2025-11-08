<?php
require_once "../config.php"; // Sesuaikan path

// Ambil id mata kuliah dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID mata kuliah tidak valid.</div>";
    exit;
}

try {
    // Query simpel, tidak perlu JOIN
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $matkul = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$matkul) {
        echo "<div class='alert alert-warning'>Data mata kuliah tidak ditemukan.</div>";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Query error: " . htmlspecialchars($e.getMessage()) . "</div>";
    exit;
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Detail Mata Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=mata-kuliah">Mata Kuliah</a></li>
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
                            <h4 class="mb-0">Detail Mata Kuliah</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Kode Mata Kuliah</th>
                                    <td><?= htmlspecialchars($matkul['kode_mk']) ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Mata Kuliah</th>
                                    <td><?= htmlspecialchars($matkul['nama_mk']) ?></td>
                                </tr>
                                <tr>
                                    <th>Jumlah SKS</th>
                                    <td><?= htmlspecialchars($matkul['sks']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer text-end">
                            <a href="./?p=matakuliah" class="btn btn-primary text-white">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="./?p=editmatakuliah&id=<?= $matkul['id'] ?>" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="./?p=hapusmatakuliah&id=<?= $matkul['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Yakin mau hapus data mata kuliah ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
<?php
require_once "../config.php"; // Sesuaikan path

// Ambil id kelas dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID kelas tidak valid.</div>";
    exit;
}

try {
    // Query simpel, tidak perlu JOIN
    $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $kelas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kelas) {
        echo "<div class='alert alert-warning'>Data kelas tidak ditemukan.</div>";
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
                <div class="col-sm-6"><h3 class="mb-0">Detail Kelas</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=kelas">Data Kelas</a></li>
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
                            <h4 class="mb-0">Detail Kelas</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%;">Nama Kelas</th>
                                    <td><?= htmlspecialchars($kelas['kelas']) ?></td>
                                </tr>
                                <tr>
                                    <th>Angkatan</th>
                                    <td><?= htmlspecialchars($kelas['angkatan']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer text-end">
                            <a href="./?p=kelas" class="btn btn-primary text-white">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <a href="./?p=edit-kelas&id=<?= $kelas['id'] ?>" class="btn btn-warning text-white">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="./?p=hapus-kelas&id=<?= $kelas['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('⚠️ Yakin mau hapus data kelas ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
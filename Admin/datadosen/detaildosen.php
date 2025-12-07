<?php
require_once "../config.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>ID dosen tidak ditemukan.</div>";
    exit;
}

try {
    // Ambil data lengkap dosen + user
    $stmt = $pdo->prepare("
      SELECT d.*, u.username, u.email, u.role, u.created_at, u.updated_at
      FROM dosen d
      JOIN users u ON d.user_id = u.id
      WHERE d.id = ?
    ");
    $stmt->execute([$id]);
    $dosen = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dosen) {
        echo "<div class='alert alert-warning'>Data dosen tidak ditemukan.</div>";
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
                    <div class="col-sm-6"><h3 class="mb-0">Detail Dosen</h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item"><a href="./?p=dosen">Dosen</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail Dosen</li>
                        </ol>
                    </div>
                    </div>
                </div>
            </div>

        <div class="app-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-user-tie me-2"></i> Detail Dosen</h4>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive"> <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                <tbody>
                    <tr>
                        <th style="width: 30%;" class="ps-4 bg-light">Nama Lengkap</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['nama_lengkap']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">NIDN</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['nidn']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">Email</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['email']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">Username</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['username']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">Role</th>
                        <td class="pe-4">
                            <span class="badge bg-info"><?= htmlspecialchars($dosen['role']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">Dibuat pada</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['created_at']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-4 bg-light">Terakhir diperbarui</th>
                        <td class="pe-4"><?= htmlspecialchars($dosen['updated_at']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer text-end bg-white border-top-0 py-3">
        <a href="./?p=dosen" class="btn btn-secondary me-1">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <a href="./?p=editdsn&id=<?= $dosen['id'] ?>" class="btn btn-warning text-white me-1">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="./?p=hapus-dsn&id=<?= $dosen['id'] ?>" 
           class="btn btn-danger"
           onclick="return confirm('⚠️ Yakin mau hapus data dosen ini? Data akan hilang permanen.');">
            <i class="fas fa-trash me-1"></i> Hapus
        </a>
    </div>
</div>
                    </div>
                </div>
            </div>
        </main>
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
                            </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 30%;">Nama Lengkap</th>
                                        <td><?= htmlspecialchars($dosen['nama_lengkap']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>NIDN</th>
                                        <td><?= htmlspecialchars($dosen['nidn']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= htmlspecialchars($dosen['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td><?= htmlspecialchars($dosen['username']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td><?= htmlspecialchars($dosen['role']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat pada</th>
                                        <td><?= htmlspecialchars($dosen['created_at']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir diperbarui</th>
                                        <td><?= htmlspecialchars($dosen['updated_at']) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer text-end">
                                <a href="./?p=dosen" class="btn btn-primary text-white">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <a href="./?p=editdsn&id=<?= $dosen['id'] ?>" class="btn btn-warning text-white">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="./?p=hapus-dsn&id=<?= $dosen['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('⚠️ Yakin mau hapus data dosen ini? Data akan hilang permanen.');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
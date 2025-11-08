<?php
require_once "../config.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>ID dosen tidak ditemukan.</div>";
    exit;
}

try {
    // Ambil data dosen + user
    $stmt = $pdo->prepare("
      SELECT d.*, u.username, u.email
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

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama_lengkap = trim($_POST["nama_lengkap"]);
        $nidn = trim($_POST["nidn"]);
        $email = trim($_POST["email"]);
        $username = trim($_POST["username"]);

        $pdo->beginTransaction();
        try {
            // Update tabel users
            $stmtUser = $pdo->prepare("UPDATE users SET username=?, email=?, updated_at=NOW() WHERE id=?");
            $stmtUser->execute([$username, $email, $dosen['user_id']]);

            // Update tabel dosen
            $stmtDosen = $pdo->prepare("UPDATE dosen SET nama_lengkap=?, nidn=? WHERE id=?");
            $stmtDosen->execute([$nama_lengkap, $nidn, $id]);

            $pdo->commit();
            echo "
                <div class='alert alert-success'>Data dosen berhasil diperbarui!</div>
                <script>
                    setTimeout(() => {
                        window.location.href = '?p=dosen';
                    }, 1000);
                </script>
            ";
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Gagal menyimpan data: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Edit Dosen</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="./?p=dosen">Dosen</a></li>
                        <li class="breadcrumb-item active">Edit Dosen</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Form Edit Dosen</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($dosen['nama_lengkap']) ?>" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">NIDN</label>
                                    <input type="text" name="nidn" value="<?= htmlspecialchars($dosen['nidn']) ?>" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($dosen['email']) ?>" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" value="<?= htmlspecialchars($dosen['username']) ?>" class="form-control" required>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="?p=dosen" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
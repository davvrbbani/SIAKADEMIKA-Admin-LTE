<?php
require_once "../config.php";
require_login(); // <-- 1. DITAMBAHKAN UNTUK KEAMANAN

// Ambil ID mahasiswa dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID mahasiswa tidak valid.</div>";
    exit;
}

try {
    // Ambil data mahasiswa
    $stmt = $pdo->prepare("
    SELECT 
        m.*, 
        k.id AS kelas_id, 
        k.kelas, 
        k.angkatan, 
        u.username, 
        u.email, 
        u.role 
    FROM mahasiswa m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN kelas k ON m.kelas_id = k.id
    WHERE m.id = ?
    LIMIT 1
    ");
    $stmt->execute([$id]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mhs) {
        echo "<div class='alert alert-warning'>Data mahasiswa tidak ditemukan.</div>";
        exit;
    }

    // Ambil semua daftar kelas untuk dropdown
    $kelasStmt = $pdo->query("SELECT id, kelas, angkatan FROM kelas ORDER BY angkatan DESC");
    $kelasList = $kelasStmt->fetchAll(PDO::FETCH_ASSOC);

    // Jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $nim = trim($_POST['NIM']);
        $semester = trim($_POST['semester']);
        $kelas_id = intval($_POST['kelas_id']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $role = trim($_POST['role']);

        // Validasi sederhana
        if ($nama_lengkap === '' || $nim === '' || $semester === '') {
            echo "<div class='alert alert-danger'>Semua field wajib diisi!</div>";
        } else {
            // Update tabel mahasiswa
            $pdo->beginTransaction();
            try {
                $updateMhs = $pdo->prepare("
                    UPDATE mahasiswa 
                    SET nama_lengkap = ?, NIM = ?, semester = ?, kelas_id = ?
                    WHERE id = ?
                ");
                $updateMhs->execute([$nama_lengkap, $nim, $semester, $kelas_id, $id]);
                
                // Update user
                $updateUser = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, role = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $updateUser->execute([$username, $email, $role, $mhs['user_id']]);

                $pdo->commit();

                // ================ 2. INI KODE LOG-NYA ================
                $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
                $pesan_log = "memperbarui data mahasiswa: $nama_lengkap (NIM: $nim)";
                log_activity($pdo, $admin_id, $pesan_log);
                // ======================================================
                
                echo "
                <script>alert('Data mahasiswa berhasil diperbarui!');
                    window.location.href = '?p=mahasiswa';
                </script>";
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Gagal menyimpan data: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!--begin::App Main-->
<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Edit Mahasiswa</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Edit Mahasiswa</li>
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
              <h4 class="mb-0">Form Edit Mahasiswa</h4>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Nama Lengkap</label>
                  <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($mhs['nama_lengkap']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">NIM</label>
                  <input type="text" name="NIM" class="form-control" value="<?= htmlspecialchars($mhs['NIM']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Semester</label>
                  <input type="number" name="semester" class="form-control" value="<?= htmlspecialchars($mhs['semester']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Kelas & Angkatan</label>
                  <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelasList as $k): ?>
                      <option value="<?= $k['id'] ?>" <?= ($mhs['kelas_id'] == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['kelas'] . ' (' . $k['angkatan'] . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($mhs['username']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($mhs['email']) ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Role</label>
                  <select name="role" class="form-select">
                    <option value="mahasiswa" <?= $mhs['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                    <option value="admin" <?= $mhs['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                  </select>
                </div>

                <div class="d-flex justify-content-between">
                  <a href="?p=mahasiswa" class="btn btn-secondary">
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

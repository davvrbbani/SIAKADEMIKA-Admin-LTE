<?php
require_once "../config.php";

$error_message = ''; // 1. Siapkan variabel error

// 3. Siapkan variabel untuk menampung data form (re-population)
$nama_lengkap = '';
$nim = '';
$semester = '1'; // Default value
$kelas_id = 0;
$username = '';
$email = '';

// Ambil semua daftar kelas untuk dropdown
// (Ini aman diletakkan di atas, tidak tergantung POST)
$kelasStmt = $pdo->query("SELECT id, kelas, angkatan FROM kelas ORDER BY angkatan DESC");
$kelasList = $kelasStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3. (Modifikasi) Ambil data dan simpan ke variabel
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nim = trim($_POST['NIM']);
    $semester = trim($_POST['semester']);
    $kelas_id = intval($_POST['kelas_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = 'mahasiswa';

    // 2. (Modifikasi) Ganti 'echo' dengan mengisi variabel error
    if ($nama_lengkap === '' || $nim === '' || $username === '' || $email === '' || $password === '' || $kelas_id <= 0) {
        $error_message = 'Semua field (termasuk kelas) wajib diisi!';
    } else {
        try {
            $pdo->beginTransaction();

            // 1️⃣ Simpan ke tabel users
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertUser = $pdo->prepare("
                INSERT INTO users (username, email, password, role, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $insertUser->execute([$username, $email, $hashedPassword, $role]);

            $user_id = $pdo->lastInsertId();

            // 2️⃣ Simpan ke tabel mahasiswa
            $insertMahasiswa = $pdo->prepare("
                INSERT INTO mahasiswa (user_id, NIM, nama_lengkap, semester, kelas_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertMahasiswa->execute([$user_id, $nim, $nama_lengkap, $semester, $kelas_id]);

            $pdo->commit();

            echo "<script>
                    alert('✅ Data mahasiswa berhasil ditambahkan!'); 
                    window.location.href='./?p=mahasiswa';
                  </script>";
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            // 2. (Modifikasi) Ganti 'echo' dengan mengisi variabel error
            $error_message = "Gagal menyimpan data: " . addslashes($e->getMessage());
        }
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Mahasiswa</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Tambah Mahasiswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <?php if (!empty($error_message)): ?>
                        <script>
                            // Tampilkan pop-up alert
                            alert('<?php echo $error_message; ?>');
                        </script>
                    <?php endif; ?>

                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Form Tambah Mahasiswa</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" class="form-control" required
                                           value="<?= htmlspecialchars($nama_lengkap) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">NIM</label>
                                    <input type="text" name="NIM" class="form-control" required
                                           value="<?= htmlspecialchars($nim) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <input type="number" name="semester" class="form-control" required
                                           value="<?= htmlspecialchars($semester) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Kelas & Angkatan</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($kelasList as $k): ?>
                                            <option value="<?= $k['id'] ?>" <?= ($k['id'] == $kelas_id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($k['kelas'] . ' (' . $k['angkatan'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <hr>
                                <h5 class="text-primary mt-4">Akun Mahasiswa</h5>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required
                                           value="<?= htmlspecialchars($username) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required
                                           value="<?= htmlspecialchars($email) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
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
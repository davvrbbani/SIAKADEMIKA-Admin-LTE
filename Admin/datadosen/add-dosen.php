<?php
require_once "../config.php";

$error_message = ''; // 1. Siapkan variabel error

// Siapkan variabel untuk menampung data form jika error
$nama_lengkap = '';
$nidn = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil Data Dosen dan simpan ke variabel
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nidn = trim($_POST['nidn']);
    
    // Ambil Data Akun dan simpan ke variabel
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'dosen';

    if ($nama_lengkap === '' || $nidn === '' || $email === '' || $username === '' || $password === '') {
        // 2. Isi variabel error (bukan echo)
        $error_message = 'Semua field wajib diisi!';
    } else {
        try {
            $pdo->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertUser = $pdo->prepare(
                "INSERT INTO users (username, email, password, role, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())"
            );
            $insertUser->execute([$username, $email, $hashedPassword, $role]);
            $user_id = $pdo->lastInsertId();

            $insertDosen = $pdo->prepare(
                "INSERT INTO dosen (user_id, nidn, nama_lengkap)
                 VALUES (?, ?, ?)"
            );
            $insertDosen->execute([$user_id, $nidn, $nama_lengkap]);

            $pdo->commit();
            // LOG ACTIVITY - TAMBAH DOSEN
            // ================ 2. INI KODE LOG-NYA ================
            $admin_id = $_SESSION['user_id']; // Ambil ID admin yang login
            $pesan_log = "menambahkan dosen baru: $nama_lengkap (NIDN: $nidn)";
            log_activity($pdo, $admin_id, $pesan_log);
            // ======================================================

            echo "<script>
                    alert('✅ Data dosen berhasil ditambahkan!'); 
                    window.location.href='./?p=dosen';
                  </script>";
            exit; 

        } catch (PDOException $e) {
            $pdo->rollBack();
            // 2. (Modifikasi) Isi variabel error dengan pesan dari database
            // addslashes() penting agar tidak merusak string JavaScript
            $error_message = "Gagal menyimpan data: " . addslashes($e->getMessage());
        }
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Tambah Dosen</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item"><a href="?p=dosen">Dosen</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah Dosen</li>
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
                            <h4 class="mb-0">Form Tambah Dosen</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <h5 class="text-primary">Data Dosen</h5>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" class="form-control" required
                                           value="<?= htmlspecialchars($nama_lengkap) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">NIDN</label>
                                    <input type="text" name="nidn" class="form-control" required
                                           value="<?= htmlspecialchars($nidn) ?>">
                                </div>

                                <hr>
                                
                                <h5 class="text-primary mt-4">Akun Dosen</h5>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required
                                           value="<?= htmlspecialchars($email) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required
                                           value="<?= htmlspecialchars($username) ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
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
<?php
// ==============================================================
// 1. KONFIGURASI & LOGIKA BACKEND
// ==============================================================
require_once "../config.php"; 

require_login(); 
if ($_SESSION['user_role'] !== 'dosen') {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- HANDLE POST REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. LOGIC UPDATE PROFIL (Nama, Email, Foto)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        try {
            $pdo->beginTransaction();
            
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $email        = trim($_POST['email']);

            // Update Tabel Dosen
            $stmtDosen = $pdo->prepare("UPDATE dosen SET nama_lengkap = ? WHERE user_id = ?");
            $stmtDosen->execute([$nama_lengkap, $user_id]);

            // Update Tabel Users (Email)
            $stmtUser = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmtUser->execute([$email, $user_id]);

            // Update Foto (Jika ada)
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) throw new Exception("Format foto harus JPG/PNG.");
                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) throw new Exception("Maksimal ukuran 2MB.");

                $target_dir = "../uploads/profile/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                // Hapus foto lama
                $cekFoto = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                $cekFoto->execute([$user_id]);
                $oldFoto = $cekFoto->fetchColumn();
                if ($oldFoto && file_exists("../" . $oldFoto)) unlink("../" . $oldFoto);

                $new_filename = "dosen_" . $user_id . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $new_filename)) {
                    $db_path = "uploads/profile/" . $new_filename;
                    $stmtImg = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmtImg->execute([$db_path, $user_id]);
                }
            }

            $pdo->commit();
            $msg = "Data profil berhasil diperbarui!";
            $msg_type = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal update profil: " . $e->getMessage();
            $msg_type = "danger";
        }
    }

    // B. LOGIC UPDATE PASSWORD (Terpisah)
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        try {
            $new_pass = trim($_POST['new_password']);
            $confirm_pass = trim($_POST['confirm_password']);

            if (strlen($new_pass) < 6) throw new Exception("Password minimal 6 karakter.");
            if ($new_pass !== $confirm_pass) throw new Exception("Konfirmasi password tidak cocok.");

            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmtPass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtPass->execute([$hashed_pass, $user_id]);

            $msg = "Password berhasil diubah!";
            $msg_type = "success";

        } catch (Exception $e) {
            $msg = "Gagal ganti password: " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}

// --- AMBIL DATA USER ---
$stmt = $pdo->prepare("SELECT u.username, u.email, u.profile_image, d.nama_lengkap, d.nidn FROM users u JOIN dosen d ON u.id = d.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
$foto_profil = !empty($profile['profile_image']) ? "../" . $profile['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($profile['nama_lengkap']) . "&background=0D6EFD&color=fff";
?>

<style>
    /* Style Foto Profil */
    .profile-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 15px;
    }
    .profile-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .btn-upload-icon {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 35px;
        height: 35px;
        background: #0d6efd;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #fff;
        transition: 0.2s;
    }
    .btn-upload-icon:hover { background: #0b5ed7; transform: scale(1.1); }
    
    /* Tabs Custom */
    .nav-pills .nav-link { color: #495057; font-weight: 500; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: #fff; font-weight: 600; box-shadow: 0 2px 5px rgba(13,110,253,0.3); }
    
    /* Hide default file input */
    #fileInput { display: none; }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0 fw-bold text-dark">Profil Saya</h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <?php if($msg): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i> <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <div class="card-body">
                        <div class="mt-4">
                            <div class="profile-container">
                                <img src="<?= htmlspecialchars($foto_profil) ?>" class="profile-img" id="previewImg" alt="Foto Dosen">
                                <label for="fileInput" class="btn-upload-icon" title="Ganti Foto">
                                    <i class="bi bi-camera"></i>
                                </label>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($profile['nama_lengkap']) ?></h4>
                            <p class="text-muted mb-2">Dosen Pengajar</p>
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                NIDN: <?= htmlspecialchars($profile['nidn'] ?? '-') ?>
                            </span>
                        </div>
                        <hr class="my-4">
                        <div class="text-start px-3">
                            <small class="text-muted text-uppercase fw-bold ls-1">Info Akun</small>
                            <div class="mt-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light p-2 rounded me-3 text-primary"><i class="bi bi-person-circle"></i></div>
                                    <div>
                                        <small class="d-block text-muted">Username</small>
                                        <span class="fw-bold"><?= htmlspecialchars($profile['username']) ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded me-3 text-primary"><i class="bi bi-envelope"></i></div>
                                    <div>
                                        <small class="d-block text-muted">Email</small>
                                        <span class="fw-bold text-break"><?= htmlspecialchars($profile['email']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white p-1 border-bottom-0">
                        <ul class="nav nav-pills p-2" id="profileTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-bio" data-bs-toggle="pill" href="#bio" role="tab">
                                    <i class="bi bi-person-badge me-2"></i>Tentang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-edit" data-bs-toggle="pill" href="#edit" role="tab">
                                    <i class="bi bi-person-gear me-2"></i>Edit Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-pass" data-bs-toggle="pill" href="#pass" role="tab">
                                    <i class="bi bi-file-lock me-2"></i>Ganti Password
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="bio" role="tabpanel">
                                <h5 class="fw-bold text-primary mb-4">Informasi Pribadi</h5>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Nama Lengkap</label>
                                    <div class="col-sm-8 fw-bold"><?= htmlspecialchars($profile['nama_lengkap']) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Nomor Induk (NIDN)</label>
                                    <div class="col-sm-8"><?= htmlspecialchars($profile['nidn']) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Alamat Email</label>
                                    <div class="col-sm-8"><?= htmlspecialchars($profile['email']) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Status</label>
                                    <div class="col-sm-8"><span class="badge bg-success">Aktif Mengajar</span></div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="edit" role="tabpanel">
                                <h5 class="fw-bold text-primary mb-4">Perbarui Data Diri</h5>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <input type="file" name="profile_image" id="fileInput" accept="image/*">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">NAMA LENGKAP</label>
                                        <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($profile['nama_lengkap']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">NIDN (Tidak dapat diubah)</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($profile['nidn']) ?>" disabled>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted">EMAIL</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="pass" role="tabpanel">
                                <h5 class="fw-bold text-danger mb-4">Keamanan Akun</h5>
                                <div class="alert alert-warning small">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Pastikan password baru Anda kuat dan tidak mudah ditebak.
                                </div>

                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">PASSWORD BARU</label>
                                        <input type="password" class="form-control" name="new_password" required minlength="6" placeholder="Minimal 6 karakter">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted">ULANGI PASSWORD BARU</label>
                                        <input type="password" class="form-control" name="confirm_password" required placeholder="Ketik ulang password baru">
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="fas fa-key me-2"></i>Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview Foto Real-time
    document.getElementById('fileInput').onchange = function (evt) {
        var tgt = evt.target || window.event.srcElement,
            files = tgt.files;

        if (FileReader && files && files.length) {
            var fr = new FileReader();
            fr.onload = function () {
                document.getElementById('previewImg').src = fr.result;
            }
            fr.readAsDataURL(files[0]);
            
            // Pindah ke tab "Edit Profil" otomatis jika user ganti foto
            var triggerTab = new bootstrap.Tab(document.querySelector('#tab-edit'))
            triggerTab.show()
        }
    }
</script>
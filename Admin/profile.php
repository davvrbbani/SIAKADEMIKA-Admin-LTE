<?php
// ==============================================================
// 1. KONFIGURASI & LOGIKA BACKEND
// ==============================================================
require_once "../config.php"; 

require_login(); 
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- HANDLE POST REQUESTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. LOGIC UPDATE PROFIL (Username, Email, Foto)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        try {
            $pdo->beginTransaction();
            
            $username = trim($_POST['username']);
            $email    = trim($_POST['email']);

            // Cek duplikasi Username/Email (kecuali milik sendiri)
            $cek = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $cek->execute([$username, $email, $user_id]);
            if ($cek->rowCount() > 0) {
                throw new Exception("Username atau Email sudah digunakan user lain.");
            }

            // Update Tabel Users
            $stmtUser = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmtUser->execute([$username, $email, $user_id]);

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

                $new_filename = "admin_" . $user_id . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $new_filename)) {
                    $db_path = "uploads/profile/" . $new_filename;
                    $stmtImg = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmtImg->execute([$db_path, $user_id]);
                }
            }

            $pdo->commit();
            
            // Update Session jika username berubah
            $_SESSION['user_name'] = $username;
            
            $msg = "Profil Admin berhasil diperbarui!";
            $msg_type = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal update profil: " . $e->getMessage();
            $msg_type = "danger";
        }
    }

    // B. LOGIC UPDATE PASSWORD
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
$stmt = $pdo->prepare("SELECT username, email, profile_image, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Foto Profil Default
$foto_profil = !empty($profile['profile_image']) ? "../" . $profile['profile_image'] : "https://ui-avatars.com/api/?name=" . urlencode($profile['username']) . "&background=dc3545&color=fff";
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
        background: #dc3545; /* Warna Merah untuk Admin */
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #fff;
        transition: 0.2s;
    }
    .btn-upload-icon:hover { background: #bb2d3b; transform: scale(1.1); }
    
    /* Tabs Custom Admin */
    .nav-pills .nav-link { color: #495057; font-weight: 500; }
    .nav-pills .nav-link.active { background-color: #dc3545; color: #fff; font-weight: 600; box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3); }
    
    /* Hide default file input */
    #fileInput { display: none; }
    
    /* Admin Badge */
    .badge-admin { background: linear-gradient(45deg, #dc3545, #ff6b6b); border: none; }
</style>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
            <div class="col-sm-6"><h3 class="mb-0 fw-bold text-dark">Profil Saya</h3></div>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>

<div class="app-content">
    <div class="container-fluid">
        
        <?php if($msg): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle me-2"></i> <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <div class="card-body">
                        <div class="mt-4">
                            <div class="profile-container">
                                <img src="<?= htmlspecialchars($foto_profil) ?>" class="profile-img" id="previewImg" alt="Foto Admin">
                                <label for="fileInput" class="btn-upload-icon" title="Ganti Foto">
                                    <i class="bi bi-camera"></i>
                                </label>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($profile['username']) ?></h4>
                            <p class="text-muted mb-2">Administrator Sistem</p>
                            <span class="badge badge-admin px-3 py-2 rounded-pill">
                                <i class="bi bi-shield-fill"></i> Super User
                            </span>
                        </div>
                        <hr class="my-4">
                        <div class="text-start px-3">
                            <small class="text-muted text-uppercase fw-bold ls-1">Detail Akun</small>
                            <div class="mt-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light p-2 rounded me-3 text-danger"><i class="bi bi-calendar"></i></div>
                                    <div>
                                        <small class="d-block text-muted">Bergabung Sejak</small>
                                        <span class="fw-bold"><?= date('d F Y', strtotime($profile['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light p-2 rounded me-3 text-danger"><i class="bi bi-envelope"></i></div>
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
                                    <i class="bi bi-info-circle me-2"></i>Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-edit" data-bs-toggle="pill" href="#edit" role="tab">
                                    <i class="bi bi-person-gear me-2"></i>Edit Profil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-pass" data-bs-toggle="pill" href="#pass" role="tab">
                                    <i class="bi bi-key me-2"></i>Ganti Password
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="bio" role="tabpanel">
                                <h5 class="fw-bold text-danger mb-4">Status Akun</h5>
                                <div class="alert alert-light border-start border-danger border-4 shadow-sm">
                                    <strong><i class="bi bi-user-shield me-2"></i> Role: Administrator</strong>
                                    <p class="mb-0 mt-1 text-muted">Anda memiliki akses penuh untuk mengelola data mahasiswa, dosen, mata kuliah, dan sistem akademik.</p>
                                </div>
                                
                                <div class="row mb-3 mt-4">
                                    <label class="col-sm-4 text-muted">Username</label>
                                    <div class="col-sm-8 fw-bold"><?= htmlspecialchars($profile['username']) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Email</label>
                                    <div class="col-sm-8"><?= htmlspecialchars($profile['email']) ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 text-muted">Terdaftar</label>
                                    <div class="col-sm-8"><?= date('d M Y H:i', strtotime($profile['created_at'])) ?> WIB</div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="edit" role="tabpanel">
                                <h5 class="fw-bold text-danger mb-4">Update Informasi Admin</h5>
                                
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <input type="file" name="profile_image" id="fileInput" accept="image/*">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">USERNAME (Digunakan untuk Login)</label>
                                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($profile['username']) ?>" required>
                                        <small class="text-muted">Pastikan username unik dan mudah diingat.</small>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-muted">EMAIL</label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-danger px-4">
                                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="pass" role="tabpanel">
                                <h5 class="fw-bold text-danger mb-4">Keamanan Akun</h5>
                                <div class="alert alert-warning small">
                                    <i class="bi bi-lock me-1"></i> Sebagai Admin, pastikan menggunakan password yang sangat kuat untuk melindungi data sistem.
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
                                        <button type="submit" class="btn btn-dark px-4">
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
            
            // Otomatis pindah ke tab Edit jika ganti foto
            var triggerTab = new bootstrap.Tab(document.querySelector('#tab-edit'))
            triggerTab.show()
        }
    }
</script>
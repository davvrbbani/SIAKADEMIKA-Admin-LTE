<?php
// Main config and student identity
require_once '../config.php';
require_once 'config/student_identity.php';

$profile_message = '';
$password_message = '';
$profile_error = false;
$password_error = false;

// --- HANDLE PROFILE INFO UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    
    // Basic validation
    if (!empty($nama_lengkap)) {
        try {
            // Students can only update their own name
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nama_lengkap = :nama_lengkap WHERE id = :id");
            $stmt->execute(['nama_lengkap' => $nama_lengkap, 'id' => $current_student['id']]);
            
            $profile_message = "Profil berhasil diperbarui!";
            // Refresh student data to show the new name immediately
            require 'config/student_identity.php'; 
        } catch (PDOException $e) {
            $profile_message = "Error: Gagal memperbarui profil.";
            $profile_error = true;
        }
    }
}

// --- HANDLE PASSWORD CHANGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check if new passwords match
    if ($new_password !== $confirm_password) {
        $password_message = "Password baru dan konfirmasi password tidak cocok.";
        $password_error = true;
    } else {
        // 2. Fetch current password hash from the database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        // 3. Verify the current password
        if ($user && password_verify($current_password, $user['password'])) {
            // 4. If correct, hash and update the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
            $update_stmt->execute(['password' => $new_password_hash, 'user_id' => $_SESSION['user_id']]);
            $password_message = "Password berhasil diubah!";
        } else {
            $password_message = "Password lama yang Anda masukkan salah.";
            $password_error = true;
        }
    }
}
?>

<!--begin::App Main-->
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Profil Saya</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Profile Information Column -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header"><h3 class="card-title">Informasi Profil</h3></div>
                        <form action="./?p=profile" method="POST">
                            <div class="card-body">
                                <?php if ($profile_message): ?>
                                    <div class="alert <?php echo $profile_error ? 'alert-danger' : 'alert-success'; ?>">
                                        <?php echo $profile_message; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($current_student['nama_lengkap']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">NIM</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_student['NIM']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kelas</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_student['nama_kelas']); ?>" readonly>
                                </div>
                                 <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_student['semester']); ?>" readonly>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profil</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password Column -->
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header"><h3 class="card-title">Ubah Password</h3></div>
                        <form action="./?p=profile" method="POST">
                            <div class="card-body">
                                 <?php if ($password_message): ?>
                                    <div class="alert <?php echo $password_error ? 'alert-danger' : 'alert-success'; ?>">
                                        <?php echo $password_message; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                             <div class="card-footer">
                                <button type="submit" name="change_password" class="btn btn-primary">Ubah Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

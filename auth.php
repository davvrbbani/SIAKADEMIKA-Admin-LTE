<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ============ LOGIN ============
    if (isset($_POST['login'])) {
        $identifier = trim($_POST['identifier']);
        $password   = trim($_POST['password']);

        // Cek username / email di DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :id OR email = :id LIMIT 1");
        $stmt->execute(['id' => $identifier]);
        $user = $stmt->fetch();

        // Verifikasi password (hash)
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            // Fungsi redirect sesuai role
            redirect_by_role($user['role']);
        } else {
            echo "<script>alert('Login gagal! Username/email atau password salah.'); window.location.href='index.php';</script>";
            exit;
        }
    }

    // ============ REGISTER ============
    if (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role     = trim($_POST['Role']);

        // Cek apakah username/email sudah dipakai
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $check->execute(['u' => $username, 'e' => $email]);
        if ($check->rowCount() > 0) {
            echo "<script>alert('Username atau email sudah digunakan.'); window.location.href='login.php';</script>";
            exit;
        }
        if (empty($role) || ($role !== 'mahasiswa' && $role !== 'dosen')){
            echo "<script>alert('Error: Silahkan pilih role yang valid.'); window.location.href='login.php';</script>";
            exit;
        }

        // Simpan data user baru
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, :r)");
        $stmt->execute(['u' => $username, 'e' => $email, 'p' => $password, 'r' => $role]);

        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='index.php';</script>";
        exit;
    }
}
?>

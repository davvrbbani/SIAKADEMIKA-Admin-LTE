<?php
// 1. Sertakan config dan cek login
require_once "../config.php";
require_login();

// 2. Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "ID tidak valid.";
    exit;
}

try {
    $pdo->beginTransaction();

    // 3. Ambil info data (NAMA & USER_ID) SEBELUM dihapus (untuk log)
    $stmt_get = $pdo->prepare("SELECT nama_lengkap, NIM, user_id FROM mahasiswa WHERE id = ?");
    $stmt_get->execute([$id]);
    $mhs = $stmt_get->fetch();

    if ($mhs) {
        $nama_mhs = $mhs['nama_lengkap'];
        $nim_mhs = $mhs['NIM'];
        $user_id_mhs = $mhs['user_id'];

        // 4. Hapus dari tabel mahasiswa
        $stmt_del_mhs = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
        $stmt_del_mhs->execute([$id]);

        // 5. Hapus dari tabel users
        $stmt_del_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt_del_user->execute([$user_id_mhs]);

        $pdo->commit();

        // ================ 6. INI KODE LOG-NYA ================
        $admin_id = $_SESSION['user_id'];
        $pesan_log = "menghapus mahasiswa: $nama_mhs (NIM: $nim_mhs)";
        log_activity($pdo, $admin_id, $pesan_log);
        // ======================================================

        // 7. Redirect kembali ke halaman daftar
        echo "<script>
                alert('Data mahasiswa berhasil dihapus!');
                window.location.href = './?p=mahasiswa';
              </script>";
        exit;

    } else {
        // Data tidak ditemukan
        $pdo->rollBack();
        echo "<script>
                alert('Data mahasiswa tidak ditemukan!');
                window.location.href = './?p=mahasiswa';
              </script>";
        exit;
    }

} catch (PDOException $e) {
    // Tangani error
    $pdo->rollBack();
    echo "Gagal menghapus data: " . $e->getMessage();
}
?>
<?php
require_once "../config.php";
require_login(); // 1. Tambahkan ini untuk keamanan & memastikan session ada

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=dosen';</script>";
    exit;
}

try {
    // 2. Ambil SEMUA info Dosen (termasuk nama & nidn) SEBELUM dihapus
    $stmt = $pdo->prepare("SELECT user_id, nama_lengkap, nidn FROM dosen WHERE id = ?");
    $stmt->execute([$id]);
    $dsn = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Ini adalah logika "Data Tidak Ditemukan" yang benar
    if (!$dsn) {
        // Jika data tidak ada, baru tampilkan error dan keluar.
        echo "<script>alert('Data dosen tidak ditemukan!'); window.location.href='?p=dosen';</script>";
        exit;
    }

    // 4. Jika data DITEMUKAN, simpan infonya untuk log
    $user_id = $dsn['user_id'];
    $nama_dsn = $dsn['nama_lengkap'];
    $nidn_dsn = $dsn['nidn'];

    // 5. Mulai proses hapus
    $pdo->beginTransaction();

    // Hapus data dosen
    $deleteDosen = $pdo->prepare("DELETE FROM dosen WHERE id = ?");
    $deleteDosen->execute([$id]);

    // Hapus user-nya juga
    $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->execute([$user_id]);

    $pdo->commit();

    // 6. Buat log SETELAH commit berhasil
    $admin_id = $_SESSION['user_id'];
    // Variabel $nama_dsn dan $nidn_dsn sekarang sudah PASTI ada isinya
    $pesan_log = "menghapus Dosen: $nama_dsn (NIDN: $nidn_dsn)";
    log_activity($pdo, $admin_id, $pesan_log);

    echo "<script>
        alert('Data dosen berhasil dihapus!');
        window.location.href='?p=dosen';
    </script>";
    exit; // Pastikan script berhenti di sini

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>
        alert('Gagal menghapus data: " . htmlspecialchars($e->getMessage()) . "');
        window.location.href='?p=dosen';
    </script>";
    exit; // Pastikan script berhenti di sini
}
?>
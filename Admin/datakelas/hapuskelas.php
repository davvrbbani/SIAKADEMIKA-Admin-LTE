<?php
require_once "../config.php"; // Sesuaikan path
require_login(); // 1. Tambahkan ini untuk keamanan & memastikan session ada

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=kelas';</script>";
    exit;
}

try {
    // 2. Ambil dulu data kelas (untuk log) SEBELUM dicek/dihapus
    $stmtGet = $pdo->prepare("SELECT kelas, angkatan FROM kelas WHERE id = ?");
    $stmtGet->execute([$id]);
    $kelas = $stmtGet->fetch(PDO::FETCH_ASSOC);

    // 3. Cek apakah kelasnya ada
    if (!$kelas) {
        echo "<script>
                alert('Data kelas tidak ditemukan!');
                window.location.href='?p=kelas';
              </script>";
        exit;
    }

    // 4. Simpan data untuk log (jika datanya ada)
    $nama_kelas = $kelas['kelas'];
    $angkatan = $kelas['angkatan'];

    // 5. Cek apakah kelas ini masih dipakai oleh mahasiswa (logika aslimu)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE kelas_id = ?");
    $stmtCheck->execute([$id]);
    if ($stmtCheck->fetchColumn() > 0) {
        // Jika masih dipakai, jangan hapus
        echo "<script>
                alert('Gagal menghapus! Data kelas ini masih digunakan oleh mahasiswa.');
                window.location.href='?p=kelas';
              </script>";
        exit;
    }

    // 6. Jika aman (tidak dipakai), baru hapus
    $deleteKelas = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
    $deleteKelas->execute([$id]);

    // 7. Buat log SETELAH berhasil hapus
    $admin_id = $_SESSION['user_id'];
    $pesan_log = "menghapus kelas: $nama_kelas (Angkatan: $angkatan)";
    log_activity($pdo, $admin_id, $pesan_log);

    // 8. Tampilkan pesan sukses
    echo "<script>
            alert('Data kelas berhasil dihapus!');
            window.location.href='?p=kelas';
          </script>";
    exit;

} catch (PDOException $e) {
    // Ini akan menangkap error lain, misal jika kelas terhubung ke JADWAL
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=kelas';
          </script>";
    exit;
}
?>
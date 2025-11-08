<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=kelas';</script>";
    exit;
}

try {
    // Cek dulu apakah kelas ini masih dipakai oleh mahasiswa
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

    // Jika aman (tidak dipakai), baru hapus
    $deleteKelas = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
    $deleteKelas->execute([$id]);

    echo "<script>
            alert('Data kelas berhasil dihapus!');
            window.location.href='?p=kelas';
          </script>";

} catch (PDOException $e) {
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=kelas';
          </script>";
}
?>
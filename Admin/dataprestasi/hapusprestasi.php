<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID prestasi tidak valid!'); window.location.href='?p=manage-prestasi';</script>";
    exit;
}

try {
    // Tidak ada file yang perlu dihapus (unlink), jadi langsung delete dari DB
    $deleteStmt = $pdo->prepare("DELETE FROM prestasi_mahasiswa WHERE id = ?");
    $deleteStmt->execute([$id]);

    echo "<script>
            alert('Data prestasi berhasil dihapus!');
            window.location.href='?p=manage-prestasi';
          </script>";

} catch (PDOException $e) {
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=manage-prestasi';
          </script>";
}
?>
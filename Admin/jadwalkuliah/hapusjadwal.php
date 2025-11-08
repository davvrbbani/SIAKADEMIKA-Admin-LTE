<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID jadwal tidak valid!'); window.location.href='?p=jadwal-kuliah';</script>";
    exit;
}

try {
    // Langsung hapus dari jadwal_kuliah
    // Tidak perlu cek relasi ke 'users' seperti di hapusmhs.php
    $deleteJadwal = $pdo->prepare("DELETE FROM jadwal_kuliah WHERE id = ?");
    $deleteJadwal->execute([$id]);

    // Cek apakah ada baris yang terhapus
    if ($deleteJadwal->rowCount() > 0) {
        echo "<script>
                alert('Data jadwal kuliah berhasil dihapus!');
                window.location.href='?p=jadwal-kuliah';
              </script>";
    } else {
        echo "<script>
                alert('Data jadwal tidak ditemukan atau sudah dihapus!');
                window.location.href='?p=jadwal-kuliah';
              </script>";
    }
} catch (PDOException $e) {
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=jadwal-kuliah';
          </script>";
}
?>
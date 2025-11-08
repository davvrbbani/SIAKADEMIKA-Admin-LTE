<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=mata-kuliah';</script>";
    exit;
}

try {
    // Langsung hapus dari mata_kuliah, tidak perlu cek user_id
    $deleteMatkul = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
    $deleteMatkul->execute([$id]);

    echo "<script>
            alert('Data mata kuliah berhasil dihapus!');
            window.location.href='?p=matakuliah';
          </script>";
} catch (PDOException $e) {
    // Menangkap error jika matkul terhubung ke tabel lain (foreign key)
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=matakuliah';
          </script>";
}
?>
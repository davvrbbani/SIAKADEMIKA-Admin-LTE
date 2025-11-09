<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// (Opsional) Ambil user_id admin dari session untuk logging
// $admin_id = $_SESSION['user_id']; 

if ($id <= 0) {
    echo "<script>alert('ID postingan tidak valid!'); window.location.href='?p=manage-kritik';</script>";
    exit;
}

try {
    // (Tidak ada file 'unlink' yang diperlukan)
    
    // Query ini akan menghapus postingan (ID) DAN semua balasannya (yang parent_id-nya = ID)
    $deleteStmt = $pdo->prepare("DELETE FROM kritik_saran WHERE id = ?");
    $deleteStmt->execute([$id]);

    echo "<script>
            alert('Postingan (dan semua balasannya) berhasil dihapus!');
            window.location.href='?p=manage-kritik';
          </script>";

} catch (PDOException $e) {
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=manage-kritik';
          </script>";
}
?>
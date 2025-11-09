<?php
require_once "../config.php"; // Sesuaikan path

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID materi tidak valid!'); window.location.href='?p=manage-materi';</script>";
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Ambil path file SEBELUM dihapus dari DB
    $stmt = $pdo->prepare("SELECT file_path FROM materi_kuliah WHERE id = ?");
    $stmt->execute([$id]);
    $materi = $stmt->fetch(PDO::FETCH_ASSOC);
    $file_path_to_delete = $materi['file_path'] ?? null;

    // 2. Hapus data dari Database
    $deleteStmt = $pdo->prepare("DELETE FROM materi_kuliah WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    // 3. Hapus file fisik dari server (jika ada)
    if (!empty($file_path_to_delete) && file_exists($file_path_to_delete)) {
        unlink($file_path_to_delete); // Perintah hapus file fisik
    }
    
    $pdo->commit();

    echo "<script>
            alert('Materi (dan file terkait) berhasil dihapus!');
            window.location.href='?p=manage-materi';
          </script>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>
            alert('Gagal menghapus data: " . addslashes($e->getMessage()) . "');
            window.location.href='?p=manage-materi';
          </script>";
}
?>
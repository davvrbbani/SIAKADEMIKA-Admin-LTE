<?php
require_once "../config.php"; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // 1. Ambil info file dulu sebelum hapus row DB
        $stmt = $pdo->prepare("SELECT foto_bukti FROM prestasi_mahasiswa WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Hapus file fisik jika ada
        if ($data && !empty($data['foto_bukti'])) {
            $filePath = "../" . $data['foto_bukti'];
            if (file_exists($filePath)) {
                unlink($filePath); // Hapus file dari folder
            }
        }

        // 3. Hapus dari Database
        $delStmt = $pdo->prepare("DELETE FROM prestasi_mahasiswa WHERE id = ?");
        $delStmt->execute([$id]);

        echo "<script>alert('Data dan file berhasil dihapus!'); window.location.href='?p=manage-prestasi';</script>";

    } catch (PDOException $e) {
        echo "<script>alert('Gagal hapus: " . addslashes($e->getMessage()) . "'); window.location.href='?p=manage-prestasi';</script>";
    }
} else {
    echo "<script>window.location.href='?p=manage-prestasi';</script>";
}
?>
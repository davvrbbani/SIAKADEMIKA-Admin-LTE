<?php
require_once "../config.php"; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo "<script>window.location.href='?p=manage-kritik';</script>"; exit; }

try {
    // 1. Ambil info foto dari postingan ini DAN semua balasannya
    // Kita gunakan Recursive atau simply 2 query
    $filesToDelete = [];

    // Cek foto postingan itu sendiri
    $stmt = $pdo->prepare("SELECT foto_lampiran FROM kritik_saran WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['foto_lampiran']) $filesToDelete[] = $row['foto_lampiran'];

    // Cek foto balasan-balasannya (jika ada)
    $stmtRep = $pdo->prepare("SELECT foto_lampiran FROM kritik_saran WHERE parent_id = ?");
    $stmtRep->execute([$id]);
    while ($rep = $stmtRep->fetch()) {
        if ($rep['foto_lampiran']) $filesToDelete[] = $rep['foto_lampiran'];
    }

    // 2. Hapus File Fisik
    foreach ($filesToDelete as $file) {
        $path = "../" . $file;
        if (file_exists($path)) unlink($path);
    }

    // 3. Hapus Data DB (Cascade delete biasanya otomatis, tapi jika tidak, manual delete reply dulu)
    $pdo->prepare("DELETE FROM kritik_saran WHERE parent_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM kritik_saran WHERE id = ?")->execute([$id]);

    echo "<script>alert('Terhapus!'); window.location.href='?p=manage-kritik';</script>";

} catch (PDOException $e) {
    echo "<script>alert('Gagal: " . addslashes($e->getMessage()) . "'); window.location.href='?p=manage-kritik';</script>";
}
?>
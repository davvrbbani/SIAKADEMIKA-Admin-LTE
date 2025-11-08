<?php
require_once "../config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=mahasiswa';</script>";
    exit;
}

try {
    // Ambil user_id dari mahasiswa biar bisa hapus di tabel users juga
    $stmt = $pdo->prepare("SELECT user_id FROM mahasiswa WHERE id = ?");
    $stmt->execute([$id]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mhs) {
        echo "<script>alert('Data mahasiswa tidak ditemukan!'); window.location.href='?p=mahasiswa';</script>";
        exit;
    }

    $user_id = $mhs['user_id'];

    $pdo->beginTransaction();

    // Hapus data mahasiswa
    $deleteMhs = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
    $deleteMhs->execute([$id]);

    // Hapus user-nya juga
    $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->execute([$user_id]);

    $pdo->commit();

    echo "<script>
        alert('Data mahasiswa berhasil dihapus!');
        window.location.href='?p=mahasiswa';
    </script>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>
        alert('Gagal menghapus data: " . htmlspecialchars($e->getMessage()) . "');
        window.location.href='?p=mahasiswa';
    </script>";
}
?>
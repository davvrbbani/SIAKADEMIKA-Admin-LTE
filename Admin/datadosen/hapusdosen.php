<?php
require_once "../config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href='?p=dosen';</script>";
    exit;
}

try {
    // Ambil user_id dari dosen biar bisa hapus di tabel users juga
    $stmt = $pdo->prepare("SELECT user_id FROM dosen WHERE id = ?");
    $stmt->execute([$id]);
    $dsn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dsn) {
        echo "<script>alert('Data dosen tidak ditemukan!'); window.location.href='?p=dosen';</script>";
        exit;
    }

    $user_id = $dsn['user_id'];

    $pdo->beginTransaction();

    // Hapus data dosen
    $deleteDosen = $pdo->prepare("DELETE FROM dosen WHERE id = ?");
    $deleteDosen->execute([$id]);

    // Hapus user-nya juga
    $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->execute([$user_id]);

    $pdo->commit();

    echo "<script>
        alert('Data dosen berhasil dihapus!');
        window.location.href='?p=dosen';
    </script>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>
        alert('Gagal menghapus data: " . htmlspecialchars($e->getMessage()) . "');
        window.location.href='?p=dosen';
    </script>";
}
?>

<?php
require_once "../config.php"; // Sesuaikan path
require_login(); // Pastikan admin sudah login

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID mata kuliah tidak valid!'); window.location.href='?p=matakuliah';</script>";
    exit;
}

try {
    // 1. Ambil info mata kuliah (nama/kode) SEBELUM dihapus (untuk log)
    $stmt_get = $pdo->prepare("SELECT nama_mk, kode_mk FROM mata_kuliah WHERE id = ?");
    $stmt_get->execute([$id]);
    $matkul = $stmt_get->fetch(PDO::FETCH_ASSOC);

    // 2. Cek apakah mata kuliahnya ada
    if ($matkul) {
        // 3. Simpan data untuk log
        $nama_matkul = $matkul['nama_mk'];
        $kode_matkul = $matkul['kode_mk'];

        // 4. Lakukan penghapusan
        $deleteMatkul = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $deleteMatkul->execute([$id]);
        
        // 5. Buat log SETELAH berhasil hapus
        $admin_id = $_SESSION['user_id'];
        $pesan_log = "menghapus mata kuliah: $nama_matkul (Kode: $kode_matkul)"; // Pesan log sudah benar
        log_activity($pdo, $admin_id, $pesan_log);

        // 6. Tampilkan pesan sukses
        echo "<script>
                alert('Data mata kuliah berhasil dihapus!');
                window.location.href='?p=matakuliah';
              </script>";
        exit;

    } else {
        // Mata kuliah dengan ID tersebut tidak ditemukan
        echo "<script>
                alert('Data mata kuliah tidak ditemukan!');
                window.location.href='?p=matakuliah';
              </script>";
        exit;
    }

} catch (PDOException $e) {
    // Menangkap error jika matkul terhubung ke tabel lain (foreign key)
    echo "<script>
            alert('Gagal menghapus data. Mata kuliah ini mungkin terhubung ke jadwal atau data lain.');
            window.location.href='?p=matakuliah';
          </script>";
    exit;
}
?>
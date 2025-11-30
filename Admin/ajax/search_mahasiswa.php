<?php
require_once "../../config.php";

// 1. BAGIAN PENCARIAN SUGESTI (AUTOCOMPLETE)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Cari nama di tabel mahasiswa
    // Opsional: Saya tambahkan pencarian NIM juga biar lebih enak
    $stmt = $pdo->prepare("SELECT nama_lengkap FROM mahasiswa WHERE nama_lengkap LIKE ? OR nim LIKE ? LIMIT 5");
    $stmt->execute(["%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            echo '<a href="#" class="list-group-item list-group-item-action suggestion-item" data-name="' 
                 . htmlspecialchars($row['nama_lengkap']) . '">' 
                 . htmlspecialchars($row['nama_lengkap']) . '</a>';
        }
    } else {
        echo '<div class="list-group-item disabled">Tidak ditemukan</div>';
    }
    exit;
}

// 2. BAGIAN AMBIL DETAIL DATA (SAAT KLIK)
if (isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);

    // UPDATE PENTING DI SINI:
    // Kita tambahkan LEFT JOIN ke tabel kelas dan ambil nama kelasnya
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.NIM,
            m.nama_lengkap,
            m.semester,
            u.username,
            u.email,
            k.kelas AS nama_kelas  -- Ambil nama kelas
        FROM mahasiswa m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN kelas k ON m.kelas_id = k.id -- Join ke tabel kelas
        WHERE m.nama_lengkap = ?
        LIMIT 1
    ");
    
    $stmt->execute([$nama]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kirim data dalam format JSON agar bisa dibaca JavaScript di file utama
    echo json_encode($data ?: []);
    exit;
}
?>
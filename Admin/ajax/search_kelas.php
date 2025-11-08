<?php
require_once "../../config.php"; // Path harus ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Cari nama di tabel kelas
    // (Mencari berdasarkan nama kelas ATAU angkatan)
    $stmt = $pdo->prepare("
        SELECT kelas, angkatan 
        FROM kelas 
        WHERE kelas LIKE ? OR angkatan LIKE ? 
        LIMIT 5
    ");
    $stmt->execute(["%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            $displayText = htmlspecialchars($row['kelas'] . ' (' . $row['angkatan'] . ')');
            // Data-name adalah nama kelasnya saja
            echo '<a href="#" class="list-group-item list-group-item-action suggestion-item" data-name="' 
                 . htmlspecialchars($row['kelas']) . '">' 
                 . $displayText . '</a>';
        }
    } else {
        echo '<div class="list-group-item disabled">Tidak ditemukan</div>';
    }
    exit;
}

// Bagian 2: Handle 'nama' (saat user klik suggestion)
// 'nama' di sini merujuk ke 'nama kelas' (dari data-name)
if (isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);

    // Ambil detail dari tabel kelas
    $stmt = $pdo->prepare("
        SELECT 
            id,
            kelas,
            angkatan
        FROM kelas
        WHERE kelas = ?
        LIMIT 1
    ");
    $stmt->execute([$nama]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data ?: []); // Kirim sebagai JSON
    exit;
}
?>
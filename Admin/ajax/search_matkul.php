<?php
require_once "../../config.php"; // Path harus ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Cari nama di tabel mata_kuliah
    $stmt = $pdo->prepare("SELECT nama_mk FROM mata_kuliah WHERE nama_mk LIKE ? LIMIT 5");
    $stmt->execute(["%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            // Gunakan nama_mk
            echo '<a href="#" class="list-group-item list-group-item-action suggestion-item" data-name="' 
                 . htmlspecialchars($row['nama_mk']) . '">' 
                 . htmlspecialchars($row['nama_mk']) . '</a>';
        }
    } else {
        echo '<div class="list-group-item disabled">Tidak ditemukan</div>';
    }
    exit;
}

// Bagian 2: Handle 'nama' (saat user klik suggestion)
if (isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);

    // Ambil detail dari tabel mata_kuliah
    $stmt = $pdo->prepare("
        SELECT 
            id,
            kode_mk,
            nama_mk,
            sks
        FROM mata_kuliah
        WHERE nama_mk = ?
        LIMIT 1
    ");
    $stmt->execute([$nama]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data ?: []); // Kirim sebagai JSON
    exit;
}
?>
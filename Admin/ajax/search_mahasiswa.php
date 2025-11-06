<?php
require_once "../../config.php";

if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // cari nama di tabel mahasiswa aja
    $stmt = $pdo->prepare("SELECT nama_lengkap FROM mahasiswa WHERE nama_lengkap LIKE ? LIMIT 5");
    $stmt->execute(["%$q%"]);
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

// kalau user klik salah satu nama di suggestion
if (isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);

    // ambil detail dari tabel mahasiswa aja
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nama_lengkap = ? LIMIT 1");
    $stmt->execute([$nama]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($data ?: []);
    exit;
}
?>

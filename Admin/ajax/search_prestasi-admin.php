<?php
require_once "../../config.php"; // Path ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Query JOIN untuk mencari berdasarkan Judul Prestasi atau Nama Mahasiswa
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.judul_prestasi,
            m.nama_lengkap AS nama_mahasiswa
        FROM 
            prestasi_mahasiswa AS p
        LEFT JOIN 
            mahasiswa AS m ON p.mahasiswa_id = m.id
        WHERE 
            p.judul_prestasi LIKE ? 
            OR m.nama_lengkap LIKE ? 
        LIMIT 10
    ");
    $stmt->execute(["%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            $displayText = htmlspecialchars(
                ($row['judul_prestasi'] ?? 'N/A') . 
                ' (' . 
                ($row['nama_mahasiswa'] ?? 'N/A') .
                ')'
            );
            
            echo '<a href="#" class="list-group-item list-group-item-action suggestion-item" data-id="' 
                 . htmlspecialchars($row['id']) . '">' 
                 . $displayText . '</a>';
        }
    } else {
        echo '<div class="list-group-item disabled">Tidak ditemukan</div>';
    }
    exit;
}

// Bagian 2: Handle 'id' (saat user klik salah satu sugesti)
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Query JOIN lengkap untuk mendapatkan detail satu prestasi
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            m.nama_lengkap AS nama_mahasiswa
        FROM prestasi_mahasiswa AS p
        LEFT JOIN mahasiswa AS m ON p.mahasiswa_id = m.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kirim data sebagai JSON
    echo json_encode($data ?: []); 
    exit;
}
?>
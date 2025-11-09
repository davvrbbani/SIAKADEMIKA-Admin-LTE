<?php
require_once "../../config.php"; // Path ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Query JOIN untuk mencari berdasarkan judul materi, nama matkul, atau nama dosen
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.judul,
            mk.nama_mk, 
            d.nama_lengkap AS nama_dosen
        FROM 
            materi_kuliah AS m
        LEFT JOIN 
            jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        WHERE 
            m.judul LIKE ? 
            OR mk.nama_mk LIKE ? 
            OR d.nama_lengkap LIKE ?
        LIMIT 10
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            $displayText = htmlspecialchars(
                ($row['judul'] ?? 'N/A') . 
                ' - ' . 
                ($row['nama_mk'] ?? 'N/A') .
                ' (' . ($row['nama_dosen'] ?? 'N/A') . ')'
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

    // Query JOIN lengkap untuk mendapatkan detail satu materi
    $stmt = $pdo->prepare("
        SELECT 
            m.id, m.judul, m.tipe_materi,
            mk.nama_mk, 
            d.nama_lengkap AS nama_dosen,
            k.kelas, k.angkatan
        FROM materi_kuliah AS m
        LEFT JOIN jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
        LEFT JOIN mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN dosen AS d ON jk.dosen_id = d.id
        LEFT JOIN kelas AS k ON jk.kelas_id = k.id
        WHERE m.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kirim data sebagai JSON
    echo json_encode($data ?: []); 
    exit;
}
?>
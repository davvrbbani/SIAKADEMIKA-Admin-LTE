<?php
require_once "../../config.php"; // Path ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik untuk sugesti)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Query JOIN untuk mencari berdasarkan judul, isi, nama mhs, atau nama dosen
    $stmt = $pdo->prepare("
        SELECT 
            ks.id, 
            ks.judul, 
            ks.isi,
            m.nama_lengkap AS nama_mahasiswa,
            d.nama_lengkap AS nama_dosen
        FROM 
            kritik_saran AS ks
        LEFT JOIN 
            users AS u ON ks.user_id = u.id
        LEFT JOIN 
            mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN 
            dosen AS d ON u.id = d.user_id
        WHERE 
            (ks.judul LIKE ? OR ks.isi LIKE ? OR m.nama_lengkap LIKE ? OR d.nama_lengkap LIKE ?)
            AND ks.parent_id IS NULL
        LIMIT 10
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            $pengirim = $row['nama_mahasiswa'] ?? $row['nama_dosen'] ?? 'User';
            $displayText = htmlspecialchars(
                ($row['judul'] ?? 'Postingan') . 
                ' (' . $pengirim . ')'
            );
            
            // Tampilkan sebagai <a> dengan data-id
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

    // Query JOIN lengkap untuk mendapatkan detail satu postingan
    $stmt = $pdo->prepare("
        SELECT 
            ks.id, ks.judul, ks.tipe, ks.is_anonim, ks.created_at,
            u.username AS author_username,
            m.nama_lengkap AS author_mahasiswa,
            d_author.nama_lengkap AS author_dosen,
            d_target.nama_lengkap AS target_dosen_nama
        FROM kritik_saran AS ks
        LEFT JOIN users AS u ON ks.user_id = u.id
        LEFT JOIN mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN dosen AS d_author ON u.id = d_author.user_id
        LEFT JOIN dosen AS d_target ON ks.target_dosen_id = d_target.id
        WHERE ks.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kirim data sebagai JSON
    echo json_encode($data ?: []); 
    exit;
}
?>
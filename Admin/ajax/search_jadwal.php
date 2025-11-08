<?php
require_once "../../config.php"; // Path harus ../../ dari folder Ajax

// Bagian 1: Handle 'query' (saat user mengetik untuk sugesti)
if (isset($_POST['query'])) {
    $q = trim($_POST['query']);
    
    // Query JOIN untuk mencari berdasarkan nama matkul, nama dosen, atau hari
    $stmt = $pdo->prepare("
        SELECT 
            jk.id, 
            mk.nama_mk, 
            d.nama_lengkap AS nama_dosen,
            jk.hari
        FROM 
            jadwal_kuliah AS jk
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        WHERE 
            mk.nama_mk LIKE ? 
            OR d.nama_lengkap LIKE ? 
            OR jk.hari LIKE ?
        LIMIT 10
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            // Tampilkan info yang jelas di sugesti
            $displayText = htmlspecialchars(
                ($row['nama_mk'] ?? 'N/A') . 
                ' - ' . 
                ($row['nama_dosen'] ?? 'N/A') .
                ' (' . $row['hari'] . ')'
            );
            
            // 'data-id' sangat penting untuk AJAX kedua
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

    // Query JOIN lengkap untuk mendapatkan detail satu jadwal (sama seperti di halaman utama)
    $stmt = $pdo->prepare("
        SELECT 
            jk.id, 
            jk.hari, 
            jk.jam_mulai, 
            jk.jam_selesai, 
            jk.ruangan,
            mk.nama_mk,
            d.nama_lengkap AS nama_dosen,
            k.kelas, 
            k.angkatan
        FROM 
            jadwal_kuliah AS jk
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        LEFT JOIN 
            kelas AS k ON jk.kelas_id = k.id
        WHERE 
            jk.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Kirim data sebagai JSON
    echo json_encode($data ?: []); 
    exit;
}
?>
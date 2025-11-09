<?php
// Main config for database connection ($pdo)
require_once '../config.php';

// Include student identity logic to get $current_student
// Ini akan memberi kita $current_student['kelas_id'] dan $current_student['nama_kelas']
require_once 'config/student_identity.php';

// Initialize an empty array to hold the materials
$materi_list = [];
$nama_kelas_mahasiswa = $current_student['nama_kelas'] ?? 'Kelas Anda';

// Fetch materials only if the student is assigned to a class
if (isset($current_student['kelas_id'])) {
    try {
        // === INI ADALAH QUERY BARU YANG SUDAH DIPERBAIKI ===
        // Kita cari materi yang jadwalnya (jk) sesuai dengan kelas_id mahasiswa
        $stmt = $pdo->prepare(
            "SELECT 
                m.judul, 
                m.deskripsi, 
                m.tipe_materi,
                m.file_path,
                m.link_url,
                m.created_at,
                mk.nama_mk,
                d.nama_lengkap AS nama_dosen
            FROM materi_kuliah AS m
            JOIN jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
            JOIN mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
            JOIN dosen AS d ON jk.dosen_id = d.id
            WHERE jk.kelas_id = :kelas_id
            ORDER BY m.created_at DESC" // Show newest materials first
        );
        $stmt->execute(['kelas_id' => $current_student['kelas_id']]);
        $materi_list = $stmt->fetchAll();
        // === END QUERY BARU ===

    } catch (PDOException $e) {
        die("Error: Tidak dapat mengambil data materi kuliah: " . $e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Materi Kuliah</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Materi Kuliah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Materi untuk Kelas <?php echo htmlspecialchars($nama_kelas_mahasiswa); ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($materi_list)): ?>
                                <div class="alert alert-info text-center">
                                    Belum ada materi yang diunggah untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%;">Judul Materi</th>
                                            <th style="width: 20%;">Mata Kuliah</th>
                                            <th style="width: 20%;">Dosen</th>
                                            <th style="width: 5%;" class="text-center">Tipe</th>
                                            <th style="width: 15%;">Tanggal Unggah</th>
                                            <th style="width: 15%;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($materi_list as $materi): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($materi['judul']); ?></strong>
                                                    <?php if (!empty($materi['deskripsi'])): ?>
                                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($materi['deskripsi']); ?></p>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($materi['nama_mk']); ?></td>
                                                <td><?php echo htmlspecialchars($materi['nama_dosen']); ?></td>
                                                <td class="text-center">
                                                    <?php if ($materi['tipe_materi'] == 'File'): ?>
                                                        <span class="badge bg-primary">File</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Link</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d F Y, H:i', strtotime($materi['created_at'])); ?></td>
                                                <td class="text-center">
                                                    <?php if ($materi['tipe_materi'] == 'File' && !empty($materi['file_path'])): ?>
                                                        <a href="../Admin/<?php echo htmlspecialchars($materi['file_path']); ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           target="_blank" 
                                                           rel="noopener noreferrer"
                                                           download> <i class="bi bi-download"></i> Download
                                                        </a>
                                                    <?php elseif ($materi['tipe_materi'] == 'Link' && !empty($materi['link_url'])): ?>
                                                        <a href="<?php echo htmlspecialchars($materi['link_url']); ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           target="_blank" 
                                                           rel="noopener noreferrer">
                                                            <i class="bi bi-link-45deg"></i> Buka Link
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </main>
<?php
// Main config for database connection ($pdo)
require_once '../config.php';

// Include student identity logic to get $current_student
require_once 'config/student_identity.php';

// --- LOGIC FOR FETCHING DATA TO DISPLAY ---
$kelas_id_mahasiswa = $current_student['kelas_id'] ?? 0;
$nama_kelas = $current_student['nama_kelas'] ?? 'Kelas Anda';

$prestasi_list = [];
if ($kelas_id_mahasiswa > 0) {
    try {
        // === QUERY DIPERBARUI: Ditambahkan 'p.deskripsi' ===
        $stmt = $pdo->prepare(
            "SELECT 
                p.judul_prestasi, 
                p.tingkat, 
                p.tanggal_diraih,
                p.deskripsi, 
                m.nama_lengkap AS nama_mahasiswa_juara
             FROM prestasi_mahasiswa p
             JOIN mahasiswa m ON p.mahasiswa_id = m.id
             WHERE m.kelas_id = :kelas_id 
             ORDER BY p.tanggal_diraih DESC"
        );
        $stmt->execute(['kelas_id' => $kelas_id_mahasiswa]);
        $prestasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error: Gagal mengambil daftar prestasi: " . $e->getMessage());
    }
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Prestasi Kelas</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Prestasi Kelas</li>
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
                            <h3 class="card-title">Daftar Prestasi - Kelas <?php echo htmlspecialchars($nama_kelas); ?></h3>
                        </div>
                        <div class="card-body">
                            
                            <?php if (empty($prestasi_list)): ?>
                                <div class="alert alert-info">
                                    Belum ada data prestasi yang tercatat di kelas ini.
                                </div>
                            <?php else: ?>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama Mahasiswa</th>
                                            <th>Judul Prestasi</th>
                                            <th>Tingkat</th>
                                            <th>Tanggal</th>
                                            <th style="width: 10%;" class="text-center">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prestasi_list as $prestasi): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($prestasi['nama_mahasiswa_juara']); ?></td>
                                            <td><?php echo htmlspecialchars($prestasi['judul_prestasi']); ?></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($prestasi['tingkat']); ?></span></td>
                                            <td><?php echo date('d M Y', strtotime($prestasi['tanggal_diraih'])); ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm btn-detail" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#prestasiModal"
                                                        data-bs-nama="<?= htmlspecialchars($prestasi['nama_mahasiswa_juara']) ?>"
                                                        data-bs-judul="<?= htmlspecialchars($prestasi['judul_prestasi']) ?>"
                                                        data-bs-tingkat="<?= htmlspecialchars($prestasi['tingkat']) ?>"
                                                        data-bs-tanggal="<?= date('d F Y', strtotime($prestasi['tanggal_diraih'])) ?>"
                                                        data-bs-deskripsi="<?= htmlspecialchars($prestasi['deskripsi'] ?? 'Tidak ada deskripsi.') ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
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
<div class="modal fade" id="prestasiModal" tabindex="-1" aria-labelledby="prestasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="prestasiModalLabel">Detail Prestasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%;">Nama Mahasiswa</th>
                        <td id="modalNama"></td>
                    </tr>
                    <tr>
                        <th>Judul Prestasi</th>
                        <td id="modalJudul"></td>
                    </tr>
                    <tr>
                        <th>Tingkat</th>
                        <td id="modalTingkat"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Diraih</th>
                        <td id="modalTanggal"></td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td id="modalDeskripsi" style="white-space: pre-wrap;"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Menunggu dokumen (halaman) selesai dimuat
document.addEventListener("DOMContentLoaded", function() {
    
    // Ambil referensi modal
    var prestasiModal = document.getElementById('prestasiModal');
    
    // Tambahkan event listener 'show.bs.modal'
    // Ini adalah event bawaan Bootstrap 5 yang akan menyala
    // TEPAT SEBELUM modalnya ditampilkan
    prestasiModal.addEventListener('show.bs.modal', function (event) {
        
        // 'event.relatedTarget' adalah tombol yang baru saja diklik
        var button = event.relatedTarget; 
        
        // Ambil semua data dari atribut 'data-bs-*' di tombol
        var nama = button.getAttribute('data-bs-nama');
        var judul = button.getAttribute('data-bs-judul');
        var tingkat = button.getAttribute('data-bs-tingkat');
        var tanggal = button.getAttribute('data-bs-tanggal');
        var deskripsi = button.getAttribute('data-bs-deskripsi');

        // Cari elemen di dalam modal berdasarkan ID-nya
        var modalNama = prestasiModal.querySelector('#modalNama');
        var modalJudul = prestasiModal.querySelector('#modalJudul');
        var modalTingkat = prestasiModal.querySelector('#modalTingkat');
        var modalTanggal = prestasiModal.querySelector('#modalTanggal');
        var modalDeskripsi = prestasiModal.querySelector('#modalDeskripsi');

        // Masukkan data ke dalam elemen modal
        modalNama.textContent = nama;
        modalJudul.textContent = judul;
        modalTingkat.innerHTML = '<span class="badge bg-info">' + tingkat + '</span>'; // Pakai innerHTML agar badge-nya tampil
        modalTanggal.textContent = tanggal;
        modalDeskripsi.textContent = deskripsi;
    });
});
</script>
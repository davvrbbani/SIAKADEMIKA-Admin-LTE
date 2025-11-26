<?php
// Main config for database connection ($pdo)
require_once '../config.php';

// Include student identity logic
require_once 'config/student_identity.php';

// --- LOGIC FOR FETCHING DATA ---
$kelas_id_mahasiswa = $current_student['kelas_id'] ?? 0;
$nama_kelas = $current_student['nama_kelas'] ?? 'Kelas Anda';

$prestasi_list = [];
if ($kelas_id_mahasiswa > 0) {
    try {
        // === QUERY UPDATE: Menambahkan p.foto_bukti ===
        $stmt = $pdo->prepare(
            "SELECT 
                p.judul_prestasi, 
                p.tingkat, 
                p.tanggal_diraih,
                p.deskripsi, 
                p.foto_bukti,
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Styling Kartu Prestasi */
    .prestasi-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .prestasi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    /* Area Gambar (Cover) */
    .card-img-wrapper {
        height: 180px;
        width: 100%;
        background-color: #f8f9fa;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .prestasi-card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    /* Placeholder jika tidak ada foto */
    .placeholder-icon {
        font-size: 4rem;
        color: #dee2e6;
    }

    /* Badge Tingkat */
    .badge-tingkat {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        text-transform: uppercase;
    }
    .bg-Internal { background: #6c757d; color: white; }
    .bg-Regional { background: #17a2b8; color: white; }
    .bg-Nasional { background: #ffc107; color: #343a40; }
    .bg-Internasional { background: #dc3545; color: white; }

    .card-body-custom {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .juara-name {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .prestasi-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 10px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .prestasi-date {
        font-size: 0.85rem;
        color: #adb5bd;
        margin-top: auto; /* Dorong ke bawah */
    }

    .btn-detail-card {
        width: 100%;
        border-radius: 0 0 15px 15px;
        font-weight: 600;
        padding: 12px;
    }
</style>

<main class="app-main">
    <div class="app-content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-trophy text-warning me-2"></i>Hall of Fame</h3>
                    <p class="text-muted mb-0">Daftar prestasi membanggakan dari teman sekelasmu (<?= htmlspecialchars($nama_kelas) ?>)</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="./">Home</a></li>
                        <li class="breadcrumb-item active">Prestasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row">
                <?php if (empty($prestasi_list)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-medal fa-5x text-muted opacity-25 mb-3"></i>
                        <h5 class="text-muted">Belum ada prestasi yang tercatat di kelas ini.</h5>
                        <p class="text-muted small">Ayo jadi yang pertama mencetak prestasi!</p>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($prestasi_list as $p): ?>
                        <?php 
                            // Cek Foto
                            $hasFoto = !empty($p['foto_bukti']) && file_exists('../' . $p['foto_bukti']);
                            $fotoUrl = $hasFoto ? '../' . $p['foto_bukti'] : '';
                            $badgeColor = 'bg-' . str_replace(' ', '', $p['tingkat']); // Helper untuk warna badge
                        ?>
                        
                        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                            <div class="prestasi-card">
                                <div class="card-img-wrapper">
                                    <span class="badge badge-tingkat <?= $badgeColor ?>"><?= htmlspecialchars($p['tingkat']) ?></span>
                                    
                                    <?php if($hasFoto): ?>
                                        <img src="<?= $fotoUrl ?>" class="card-img-top" alt="Bukti Prestasi">
                                    <?php else: ?>
                                        <i class="fas fa-award placeholder-icon"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body-custom">
                                    <div class="juara-name">
                                        <i class="fas fa-user-graduate me-1"></i> <?= htmlspecialchars($p['nama_mahasiswa_juara']) ?>
                                    </div>
                                    
                                    <div class="prestasi-title">
                                        <?= htmlspecialchars($p['judul_prestasi']) ?>
                                    </div>

                                    <div class="prestasi-date">
                                        <i class="far fa-calendar-alt me-1"></i> <?= date('d F Y', strtotime($p['tanggal_diraih'])) ?>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary btn-detail-card btn-detail" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#prestasiModal"
                                        data-nama="<?= htmlspecialchars($p['nama_mahasiswa_juara']) ?>"
                                        data-judul="<?= htmlspecialchars($p['judul_prestasi']) ?>"
                                        data-tingkat="<?= htmlspecialchars($p['tingkat']) ?>"
                                        data-tanggal="<?= date('d F Y', strtotime($p['tanggal_diraih'])) ?>"
                                        data-deskripsi="<?= htmlspecialchars($p['deskripsi'] ?? '-') ?>"
                                        data-foto="<?= $fotoUrl ?>"> Lihat Detail
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<div class="modal fade" id="prestasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="prestasiModalLabel"><i class="bi bi-stars"></i>Detail Prestasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    
                    <div class="col-md-6 bg-light d-flex align-items-center justify-content-center p-3" style="min-height: 300px;">
                        <div id="previewContainer" class="text-center w-100">
                            <img id="modalImg" src="" class="img-fluid rounded shadow-sm" style="max-height: 350px; display: none;">
                            <div id="noImg" class="text-muted" style="display: none;">
                                <i class="fas fa-image fa-4x mb-2"></i><br>Tidak ada foto bukti.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 p-4">
                        <h5 class="fw-bold text-primary mb-1" id="modalJudul">-</h5>
                        <span class="badge bg-warning text-dark mb-3" id="modalTingkat">-</span>

                        <hr class="my-3">

                        <div class="mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Peraih Prestasi</small>
                            <div class="fs-5 text-dark" id="modalNama">-</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Tanggal</small>
                            <div class="text-dark" id="modalTanggal">-</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold">Deskripsi</small>
                            <p class="text-dark mb-0" id="modalDeskripsi" style="white-space: pre-wrap;">-</p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var prestasiModal = document.getElementById('prestasiModal');
    
    prestasiModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; 
        
        // Ambil data dari atribut
        var nama = button.getAttribute('data-nama');
        var judul = button.getAttribute('data-judul');
        var tingkat = button.getAttribute('data-tingkat');
        var tanggal = button.getAttribute('data-tanggal');
        var deskripsi = button.getAttribute('data-deskripsi');
        var fotoUrl = button.getAttribute('data-foto');

        // Set text content
        document.getElementById('modalNama').textContent = nama;
        document.getElementById('modalJudul').textContent = judul;
        document.getElementById('modalTingkat').textContent = tingkat;
        document.getElementById('modalTanggal').textContent = tanggal;
        document.getElementById('modalDeskripsi').textContent = deskripsi;

        // Logic Tampilan Gambar
        var imgEl = document.getElementById('modalImg');
        var noImgEl = document.getElementById('noImg');

        if (fotoUrl && fotoUrl !== '') {
            // Jika file PDF (Opsional, handling sederhana)
            if(fotoUrl.toLowerCase().endsWith('.pdf')){
                imgEl.style.display = 'none';
                noImgEl.innerHTML = '<i class="fas fa-file-pdf fa-4x text-danger mb-2"></i><br><a href="'+fotoUrl+'" target="_blank" class="btn btn-sm btn-outline-danger">Lihat File PDF</a>';
                noImgEl.style.display = 'block';
            } else {
                // Jika Gambar
                imgEl.src = fotoUrl;
                imgEl.style.display = 'block';
                noImgEl.style.display = 'none';
            }
        } else {
            imgEl.style.display = 'none';
            noImgEl.innerHTML = '<i class="fas fa-image fa-4x mb-2"></i><br>Tidak ada foto bukti.';
            noImgEl.style.display = 'block';
        }
    });
});
</script>
<?php
// ==============================================================
// 1. KONFIGURASI & DATA
// ==============================================================
require_once '../config.php';
require_once 'config/student_identity.php';

// Cek Login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

$materi_list = [];
$nama_kelas_mahasiswa = $current_student['nama_kelas'] ?? 'Umum';

// Fetch Data Materi
if (isset($current_student['kelas_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.judul, m.deskripsi, m.tipe_materi, 
                m.file_path, m.link_url, m.created_at,
                mk.nama_mk, d.nama_lengkap AS nama_dosen
            FROM materi_kuliah AS m
            JOIN jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
            JOIN mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
            JOIN dosen AS d ON jk.dosen_id = d.id
            WHERE jk.kelas_id = :kelas_id
            ORDER BY m.created_at DESC
        ");
        $stmt->execute(['kelas_id' => $current_student['kelas_id']]);
        $materi_list = $stmt->fetchAll();

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Nunito', sans-serif; background-color: #f4f6f9; }

    /* Card Styles (Mirip Dosen) */
    .materi-card {
        border: none;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    .materi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }

    /* Garis Aksen Atas */
    .card-top-accent { height: 6px; width: 100%; }
    .accent-file { background: linear-gradient(90deg, #4e73df, #224abe); }
    .accent-link { background: linear-gradient(90deg, #f6c23e, #dda20a); }

    .materi-body { padding: 20px; flex-grow: 1; }

    /* Icon Box */
    .icon-box {
        width: 50px; height: 50px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; margin-bottom: 15px; flex-shrink: 0;
    }
    .icon-file { background-color: #e8f0fe; color: #1a73e8; }
    .icon-link { background-color: #fef7e0; color: #f9ab00; }

    /* Typography */
    .materi-title {
        font-size: 1.1rem; font-weight: 700; color: #2c3e50;
        margin-bottom: 8px; line-height: 1.4;
    }
    .materi-meta { font-size: 0.85rem; color: #6c757d; margin-bottom: 12px; }
    .materi-desc {
        font-size: 0.9rem; color: #555;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    /* Footer */
    .materi-footer {
        padding: 15px 20px; background-color: #f8f9fc; border-top: 1px solid #eaecf4;
        text-align: center;
    }

    /* Search */
    .search-wrapper { position: relative; }
    .search-wrapper input { border-radius: 25px; padding-left: 40px; border: 1px solid #e3e6f0; }
    .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #ccc; }
</style>

<main class="app-main">
    <div class="app-content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-0 fw-bold text-dark">Materi Kuliah</h3>
                    <p class="text-muted mb-0 small">Bahan ajar untuk kelas <strong><?= htmlspecialchars($nama_kelas_mahasiswa) ?></strong></p>
                </div>
                <div class="col-md-6 text-end d-none d-md-block">
                    <span class="badge bg-white text-dark shadow-sm px-3 py-2 border">
                        <i class="fas fa-book-reader me-1"></i> Total: <?= count($materi_list) ?> Materi
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchMateri" class="form-control" placeholder="Cari judul materi, mata kuliah, atau dosen...">
                    </div>
                </div>
            </div>

            <div class="row" id="materiContainer">
                <?php if (empty($materi_list)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted opacity-25 mb-3"></i>
                        <h5 class="text-muted">Belum ada materi yang tersedia.</h5>
                    </div>
                <?php else: ?>
                    <?php foreach ($materi_list as $m): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4 search-item">
                            <div class="materi-card">
                                <div class="card-top-accent <?= $m['tipe_materi'] == 'File' ? 'accent-file' : 'accent-link' ?>"></div>
                                
                                <div class="materi-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="icon-box <?= $m['tipe_materi'] == 'File' ? 'icon-file' : 'icon-link' ?>">
                                            <i class="<?= $m['tipe_materi'] == 'File' ? 'fas fa-file-alt' : 'fas fa-link' ?>"></i>
                                        </div>
                                        <span class="badge bg-light text-secondary border rounded-pill">
                                            <?= date('d M Y', strtotime($m['created_at'])) ?>
                                        </span>
                                    </div>

                                    <h5 class="materi-title text-truncate" title="<?= htmlspecialchars($m['judul']) ?>">
                                        <?= htmlspecialchars($m['judul']) ?>
                                    </h5>
                                    
                                    <div class="materi-meta">
                                        <div class="text-truncate" title="<?= htmlspecialchars($m['nama_mk']) ?>">
                                            <i class="fas fa-book me-1"></i> <?= htmlspecialchars($m['nama_mk']) ?>
                                        </div>
                                        <div class="text-truncate" title="<?= htmlspecialchars($m['nama_dosen']) ?>">
                                            <i class="fas fa-chalkboard-teacher me-1"></i> <?= htmlspecialchars($m['nama_dosen']) ?>
                                        </div>
                                    </div>

                                    <p class="materi-desc text-muted small">
                                        <?= !empty($m['deskripsi']) ? htmlspecialchars($m['deskripsi']) : 'Tidak ada deskripsi tambahan.' ?>
                                    </p>
                                </div>

                                <div class="materi-footer">
                                    <button class="btn btn-outline-primary w-100 rounded-pill btn-detail" 
                                        data-judul="<?= htmlspecialchars($m['judul']) ?>"
                                        data-mk="<?= htmlspecialchars($m['nama_mk']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($m['deskripsi']) ?>"
                                        data-tipe="<?= htmlspecialchars($m['tipe_materi']) ?>"
                                        data-file="<?= htmlspecialchars($m['file_path']) ?>"
                                        data-link="<?= htmlspecialchars($m['link_url']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalDetail">
                                        <i class="fas fa-eye me-2"></i> Buka Materi
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="detailJudul">Lihat Materi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-1" id="detailMK">-</h6>
                    <p class="text-muted small mb-0" id="detailDeskripsi">-</p>
                </div>

                <div class="p-3 text-center" style="background-color: #e9ecef; min-height: 400px;">
                    <div id="previewArea" class="bg-white rounded shadow-sm p-2 h-100 d-flex align-items-center justify-content-center">
                        <div class="spinner-border text-primary" role="status" style="display:none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                
                <div class="p-3 text-center bg-white border-top">
                    <a href="#" id="btnDownload" class="btn btn-success px-4 shadow-sm" target="_blank" style="display:none">
                        <i class="fas fa-download me-2"></i> Download File Asli
                    </a>
                    <a href="#" id="btnLink" class="btn btn-primary px-4 shadow-sm" target="_blank" style="display:none">
                        <i class="fas fa-external-link-alt me-2"></i> Buka Tautan di Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 1. Fitur Search
    $("#searchMateri").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".search-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // 2. Fitur Preview Modal
    $(document).on('click', '.btn-detail', function() {
        // Ambil data
        let judul = $(this).data('judul');
        let mk = $(this).data('mk');
        let deskripsi = $(this).data('deskripsi') ? $(this).data('deskripsi') : 'Tidak ada deskripsi.';
        let tipe = $(this).data('tipe');
        let fileRaw = $(this).data('file'); // Path relatif dari DB
        let link = $(this).data('link');

        // Set Text
        $('#detailJudul').text(judul);
        $('#detailMK').text(mk);
        $('#detailDeskripsi').text(deskripsi);

        // Reset Tampilan
        $('#btnDownload').hide();
        $('#btnLink').hide();
        $('#previewArea').html('');

        if (tipe === 'Link' && link) {
            // --- LOGIKA LINK ---
            $('#btnLink').attr('href', link).show();
            
            // Cek Youtube
            if (link.includes('youtube.com') || link.includes('youtu.be')) {
                let videoId = link.split('v=')[1] || link.split('/').pop();
                if (videoId.indexOf('&') != -1) videoId = videoId.substring(0, videoId.indexOf('&'));
                
                $('#previewArea').html(`
                    <div class="ratio ratio-16x9 w-100">
                        <iframe src="https://www.youtube.com/embed/${videoId}" allowfullscreen class="rounded"></iframe>
                    </div>
                `);
            } else {
                // Link biasa
                $('#previewArea').html(`
                    <div class="text-center text-muted">
                        <i class="fas fa-link fa-4x mb-3 text-warning"></i><br>
                        Ini adalah tautan eksternal.<br>
                        Silakan klik tombol <b>"Buka Tautan"</b> di bawah.
                    </div>
                `);
            }

        } else if (tipe === 'File' && fileRaw) {
            // --- LOGIKA FILE ---
            // Path file: Karena kita di folder 'Mahasiswa', dan file ada di '../uploads/materi/'
            // Kita perlu naik satu level dari root admin path yang tersimpan di DB.
            // Biasanya di DB tersimpan 'uploads/materi/file.pdf'.
            // Maka dari Mahasiswa/materi.php, aksesnya adalah '../uploads/materi/file.pdf'
            
            // Cek apakah path di DB sudah ada 'uploads/' nya.
            // Asumsi di DB: uploads/materi/namafile.pdf
            let fileUrl = '../Admin/' + fileRaw; // Sesuaikan jika file diupload via Admin dan strukturnya beda
            // Jika file diupload dosen via modul dosen saya sebelumnya, pathnya '../uploads/...'.
            // Cek script upload Dosen Anda. Jika path di DB "uploads/materi/...", maka di sini "../" + path.
            fileUrl = '../' + fileRaw; 

            let ext = fileRaw.split('.').pop().toLowerCase();

            $('#btnDownload').attr('href', fileUrl).show();

            if (ext === 'pdf') {
                $('#previewArea').html(`
                    <iframe src="${fileUrl}" width="100%" height="450px" style="border:none;">
                        Browser Anda tidak mendukung preview PDF.
                    </iframe>
                `);
            } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                $('#previewArea').html(`
                    <img src="${fileUrl}" class="img-fluid rounded shadow-sm" style="max-height:400px;">
                `);
            } else if (['mp4', 'webm'].includes(ext)) {
                $('#previewArea').html(`
                    <video width="100%" controls class="rounded shadow-sm">
                        <source src="${fileUrl}" type="video/${ext}">
                        Browser tidak support video.
                    </video>
                `);
            } else {
                // Dokumen Office / Zip
                $('#previewArea').html(`
                    <div class="text-center text-muted">
                        <i class="fas fa-file-download fa-4x mb-3 text-primary"></i><br>
                        Pratinjau tidak tersedia untuk format <b>.${ext}</b>.<br>
                        Silakan download file untuk membuka.
                    </div>
                `);
            }
        } else {
             $('#previewArea').html('<div class="text-muted">Konten tidak ditemukan.</div>');
        }
    });
</script>
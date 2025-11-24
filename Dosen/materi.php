<?php
// ==============================================================
// 1. KONFIGURASI & AUTHENTICATION
// ==============================================================
require_once "../config.php"; 

require_login(); 
if ($_SESSION['user_role'] !== 'dosen') {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit;
}

$user_id_login = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE user_id = ?");
$stmt->execute([$user_id_login]);
$dosen = $stmt->fetch();

if (!$dosen) die("Error: Data Dosen tidak ditemukan.");
$id_dosen_sekarang = $dosen['id']; 

// ==============================================================
// 2. LOGIC PHP (CRUD)
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- HAPUS MATERI ---
        if (isset($_POST['action']) && $_POST['action'] === 'hapus') {
            $id_materi = intval($_POST['id_materi']);
            $cek = $pdo->prepare("SELECT m.file_path FROM materi_kuliah m JOIN jadwal_kuliah jk ON m.jadwal_kuliah_id = jk.id WHERE m.id = ? AND jk.dosen_id = ?");
            $cek->execute([$id_materi, $id_dosen_sekarang]);
            $data = $cek->fetch();

            if ($data) {
                if (!empty($data['file_path']) && file_exists("../" . $data['file_path'])) {
                    unlink("../" . $data['file_path']);
                }
                $pdo->prepare("DELETE FROM materi_kuliah WHERE id = ?")->execute([$id_materi]);
                log_activity($pdo, $user_id_login, "Menghapus materi ID: $id_materi");
                echo "<script>alert('Materi berhasil dihapus!'); window.location='./?p=materi';</script>";
            } else {
                throw new Exception("Gagal menghapus. Materi tidak ditemukan.");
            }
        }

        // --- TAMBAH / EDIT MATERI ---
        if (isset($_POST['action']) && ($_POST['action'] === 'tambah' || $_POST['action'] === 'edit')) {
            $jadwal_id   = intval($_POST['jadwal_kuliah_id']);
            $judul       = trim($_POST['judul']);
            $deskripsi   = trim($_POST['deskripsi']);
            $tipe_materi = trim($_POST['tipe_materi']);
            $link_url    = trim($_POST['link_url']);
            $file_path   = NULL;

            $cekJadwal = $pdo->prepare("SELECT id FROM jadwal_kuliah WHERE id = ? AND dosen_id = ?");
            $cekJadwal->execute([$jadwal_id, $id_dosen_sekarang]);
            if ($cekJadwal->rowCount() == 0) throw new Exception("Jadwal Kuliah tidak valid.");

            $upload_dir = "../uploads/materi/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if ($tipe_materi === 'File') {
                if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'mp4', 'jpg', 'png'];
                    
                    if (!in_array($ext, $allowed)) throw new Exception("Format file tidak diizinkan.");
                    if ($_FILES['file_materi']['size'] > 10 * 1024 * 1024) throw new Exception("Ukuran file maksimal 10MB.");

                    $new_filename = "materi_" . $jadwal_id . "_" . uniqid() . "." . $ext;
                    if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $upload_dir . $new_filename)) {
                        $file_path = "uploads/materi/" . $new_filename;
                    } else {
                        throw new Exception("Gagal upload file.");
                    }
                }
            }

            if ($_POST['action'] === 'tambah') {
                $sql = "INSERT INTO materi_kuliah (jadwal_kuliah_id, judul, deskripsi, tipe_materi, file_path, link_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $pdo->prepare($sql)->execute([$jadwal_id, $judul, $deskripsi, $tipe_materi, $file_path, ($tipe_materi == 'Link' ? $link_url : NULL)]);
                log_activity($pdo, $user_id_login, "Menambahkan materi: $judul");
                echo "<script>alert('Berhasil upload materi!'); window.location='./?p=materi';</script>";

            } elseif ($_POST['action'] === 'edit') {
                $id_materi = intval($_POST['id_materi']);
                $old_db_path = $_POST['old_file_path'];

                if ($tipe_materi == 'Link') {
                    if (!empty($old_db_path) && file_exists("../" . $old_db_path)) unlink("../" . $old_db_path);
                    $file_path = NULL;
                } elseif ($tipe_materi == 'File') {
                    if ($file_path !== NULL) {
                        if (!empty($old_db_path) && file_exists("../" . $old_db_path)) unlink("../" . $old_db_path);
                    } else {
                        $file_path = $old_db_path;
                    }
                }
                $sql = "UPDATE materi_kuliah SET jadwal_kuliah_id=?, judul=?, deskripsi=?, tipe_materi=?, file_path=?, link_url=? WHERE id=?";
                $pdo->prepare($sql)->execute([$jadwal_id, $judul, $deskripsi, $tipe_materi, $file_path, ($tipe_materi == 'Link' ? $link_url : NULL), $id_materi]);
                log_activity($pdo, $user_id_login, "Update materi: $judul");
                echo "<script>alert('Berhasil update materi!'); window.location='./?p=materi';</script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// 3. QUERY DATA
$stmt = $pdo->prepare("SELECT m.*, mk.nama_mk, k.kelas, k.angkatan, jk.hari, jk.ruangan FROM materi_kuliah m JOIN jadwal_kuliah jk ON m.jadwal_kuliah_id = jk.id JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id JOIN kelas k ON jk.kelas_id = k.id WHERE jk.dosen_id = ? ORDER BY m.created_at DESC");
$stmt->execute([$id_dosen_sekarang]);
$materiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtJadwal = $pdo->prepare("SELECT jk.id, mk.nama_mk, k.kelas, k.angkatan, jk.hari FROM jadwal_kuliah jk JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id JOIN kelas k ON jk.kelas_id = k.id WHERE jk.dosen_id = ? ORDER BY mk.nama_mk ASC");
$stmtJadwal->execute([$id_dosen_sekarang]);
$jadwalList = $stmtJadwal->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Card Styles */
    .materi-card {
        border: none;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .materi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .card-top-accent { height: 6px; width: 100%; }
    .accent-file { background: linear-gradient(90deg, #3b82f6, #1d4ed8); } 
    .accent-link { background: linear-gradient(90deg, #f59e0b, #b45309); } 

    .materi-body { padding: 20px; flex-grow: 1; }

    /* --- PERBAIKAN ICON DI SINI --- */
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center; /* Vertikal Center */
        justify-content: center; /* Horizontal Center */
        font-size: 24px; /* Ukuran Icon */
        margin-bottom: 15px;
        flex-shrink: 0;
    }
    .icon-file { background-color: #eff6ff; color: #3b82f6; } /* Biru Muda */
    .icon-link { background-color: #fef3c7; color: #d97706; } /* Kuning Muda */

    .materi-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 5px;
        line-height: 1.4;
    }
    .materi-meta {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .materi-desc {
        font-size: 0.9rem;
        color: #4b5563;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .materi-footer {
        padding: 15px 20px;
        background-color: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .search-wrapper { position: relative; }
    .search-wrapper input { border-radius: 25px; padding-left: 40px; border: 1px solid #d1d5db; }
    .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
</style>

<div class="app-content-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-0 fw-bold text-dark">Materi Kuliah</h3>
                <p class="text-muted mb-0 small">Kelola bahan ajar dan referensi untuk mahasiswa.</p>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalMateri" onclick="resetFormTambah()">
                    <i class="fas fa-plus me-2"></i> Upload Materi
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="row mb-4">
            <div class="col-md-5">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchMateri" class="form-control" placeholder="Cari judul materi, mata kuliah, atau kelas...">
                </div>
            </div>
            <div class="col-md-7 text-end d-none d-md-block">
                <span class="text-muted small">Total Materi: <strong><?= count($materiList) ?></strong></span>
            </div>
        </div>

        <div class="row" id="materiContainer">
            <?php if(empty($materiList)): ?>
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-folder-open fa-4x text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted">Belum ada materi yang diunggah.</h5>
                </div>
            <?php else: ?>
                <?php foreach($materiList as $m): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4 search-item">
                        <div class="materi-card">
                            <div class="card-top-accent <?= $m['tipe_materi'] == 'File' ? 'accent-file' : 'accent-link' ?>"></div>
                            
                            <div class="materi-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="icon-box <?= $m['tipe_materi'] == 'File' ? 'icon-file' : 'icon-link' ?>">
                                        <?php if($m['tipe_materi'] == 'File'): ?>
                                            <i class="fa-solid fa-file-lines"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-link"></i>
                                        <?php endif; ?>
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
                                        <i class="fa-solid fa-book me-1"></i> <?= htmlspecialchars($m['nama_mk']) ?>
                                    </div>
                                    <div>
                                        <i class="fa-solid fa-users me-1"></i> Kelas <?= htmlspecialchars($m['kelas']) ?>
                                    </div>
                                </div>

                                <p class="materi-desc text-muted small">
                                    <?= !empty($m['deskripsi']) ? htmlspecialchars($m['deskripsi']) : 'Tidak ada deskripsi.' ?>
                                </p>
                            </div>

                            <div class="materi-footer">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-detail" 
                                    data-judul="<?= htmlspecialchars($m['judul']) ?>"
                                    data-mk="<?= htmlspecialchars($m['nama_mk']) ?>"
                                    data-kelas="<?= htmlspecialchars($m['kelas']) ?>"
                                    data-hari="<?= htmlspecialchars($m['hari']) ?>"
                                    data-ruangan="<?= htmlspecialchars($m['ruangan']) ?>"
                                    data-deskripsi="<?= htmlspecialchars($m['deskripsi']) ?>"
                                    data-tipe="<?= htmlspecialchars($m['tipe_materi']) ?>"
                                    data-file="<?= htmlspecialchars($m['file_path']) ?>"
                                    data-link="<?= htmlspecialchars($m['link_url']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalDetail">
                                    <i class="fa-solid fa-eye me-1"></i> Lihat
                                </button>
                                
                                <div>
                                    <button class="btn btn-sm btn-light text-warning btn-edit" title="Edit"
                                        data-id="<?= $m['id'] ?>"
                                        data-jadwal="<?= $m['jadwal_kuliah_id'] ?>"
                                        data-judul="<?= htmlspecialchars($m['judul']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($m['deskripsi']) ?>"
                                        data-tipe="<?= $m['tipe_materi'] ?>"
                                        data-link="<?= htmlspecialchars($m['link_url']) ?>"
                                        data-oldfile="<?= htmlspecialchars($m['file_path']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalMateri">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus materi ini?');">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id_materi" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMateri" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Upload Materi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="formAction" value="tambah">
                    <input type="hidden" name="id_materi" id="idMateri" value="">
                    <input type="hidden" name="old_file_path" id="oldFilePath" value="">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">MATA KULIAH & KELAS</label>
                        <select name="jadwal_kuliah_id" id="inputJadwal" class="form-select" required>
                            <option value="">-- Pilih Jadwal --</option>
                            <?php foreach($jadwalList as $j): ?>
                                <option value="<?= $j['id'] ?>">
                                    <?= $j['nama_mk'] ?> - <?= $j['kelas'] ?> (<?= $j['hari'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">JUDUL MATERI</label>
                        <input type="text" name="judul" id="inputJudul" class="form-control" required placeholder="Contoh: Pengantar Algoritma">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">DESKRIPSI</label>
                        <textarea name="deskripsi" id="inputDeskripsi" class="form-control" rows="2" placeholder="Catatan singkat..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">TIPE</label>
                            <select name="tipe_materi" id="inputTipe" class="form-select">
                                <option value="File">File</option>
                                <option value="Link">Link</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3" id="groupFile">
                                <label class="form-label fw-bold small text-muted">UPLOAD FILE</label>
                                <input type="file" name="file_materi" class="form-control">
                                <div id="msgFile" class="alert alert-success py-1 px-2 mt-2 small" style="display:none;">
                                    <i class="fa-solid fa-check-circle"></i> File sudah ada.
                                </div>
                            </div>
                            <div class="mb-3" id="groupLink" style="display:none;">
                                <label class="form-label fw-bold small text-muted">URL LINK</label>
                                <input type="url" name="link_url" id="inputLink" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="detailJudul">Detail Materi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-muted d-block text-uppercase">Mata Kuliah</small>
                            <strong class="text-dark" id="detailMK">-</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block text-uppercase">Kelas</small>
                            <strong class="text-dark" id="detailKelas">-</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block text-uppercase">Jadwal</small>
                            <span class="badge bg-secondary" id="detailHari">-</span>
                        </div>
                    </div>
                    <div class="mt-2">
                         <small class="text-muted d-block text-uppercase">Deskripsi</small>
                         <span class="text-dark" id="detailDeskripsi">-</span>
                    </div>
                </div>

                <div class="p-3 text-center" style="background-color: #e9ecef; min-height: 350px;">
                    <div id="previewArea" class="bg-white rounded shadow-sm p-2 h-100 d-flex align-items-center justify-content-center">
                    </div>
                </div>
                
                <div class="p-3 text-center bg-white border-top">
                    <a href="#" id="btnDownload" class="btn btn-success px-4" target="_blank" style="display:none">
                        <i class="fa-solid fa-download me-2"></i> Download File
                    </a>
                    <a href="#" id="btnLink" class="btn btn-primary px-4" target="_blank" style="display:none">
                        <i class="fa-solid fa-external-link-alt me-2"></i> Buka Tautan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Search
    $("#searchMateri").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".search-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Toggle Tipe
    function toggleTipe() {
        let tipe = $('#inputTipe').val();
        if (tipe === 'Link') {
            $('#groupFile').hide();
            $('#groupLink').show();
        } else {
            $('#groupFile').show();
            $('#groupLink').hide();
        }
    }
    $('#inputTipe').on('change', toggleTipe);

    // Reset Form
    function resetFormTambah() {
        $('#modalTitle').text('Upload Materi Baru');
        $('#formAction').val('tambah');
        $('#idMateri').val('');
        $('#oldFilePath').val('');
        $('#inputJadwal').val('');
        $('#inputJudul').val('');
        $('#inputDeskripsi').val('');
        $('#inputTipe').val('File').trigger('change');
        $('#inputLink').val('');
        $('#msgFile').hide();
    }

    // Edit
    $(document).on('click', '.btn-edit', function() {
        $('#modalTitle').text('Edit Materi');
        $('#formAction').val('edit');
        $('#idMateri').val($(this).data('id'));
        $('#inputJadwal').val($(this).data('jadwal'));
        $('#inputJudul').val($(this).data('judul'));
        $('#inputDeskripsi').val($(this).data('deskripsi'));
        $('#oldFilePath').val($(this).data('oldfile'));
        let tipe = $(this).data('tipe');
        $('#inputTipe').val(tipe);
        $('#inputLink').val($(this).data('link'));
        toggleTipe();
        if (tipe === 'File' && $(this).data('oldfile') !== '') { $('#msgFile').show(); } else { $('#msgFile').hide(); }
    });

    // Detail & Preview
    $(document).on('click', '.btn-detail', function() {
        $('#detailMK').text($(this).data('mk'));
        $('#detailKelas').text($(this).data('kelas'));
        $('#detailHari').text($(this).data('hari') + ' (' + $(this).data('ruangan') + ')');
        $('#detailDeskripsi').text($(this).data('deskripsi') ? $(this).data('deskripsi') : 'Tidak ada deskripsi');

        let tipe = $(this).data('tipe');
        let fileRaw = $(this).data('file'); 
        let link = $(this).data('link');
        
        $('#btnDownload').hide(); $('#btnLink').hide(); $('#previewArea').html(''); 

        if (tipe === 'Link' && link) {
            $('#btnLink').attr('href', link).show();
            if (link.includes('youtube.com') || link.includes('youtu.be')) {
                let videoId = link.split('v=')[1] || link.split('/').pop();
                if(videoId.indexOf('&') != -1) videoId = videoId.substring(0, videoId.indexOf('&'));
                $('#previewArea').html(`<div class="ratio ratio-16x9 w-100"><iframe src="https://www.youtube.com/embed/${videoId}" allowfullscreen class="rounded"></iframe></div>`);
            } else {
                $('#previewArea').html('<div class="text-muted"><i class="fa-solid fa-link fa-3x mb-3"></i><br>Klik tombol "Buka Tautan" di bawah.</div>');
            }
        } else if (tipe === 'File' && fileRaw) {
            let fileUrl = '../' + fileRaw;
            let ext = fileRaw.split('.').pop().toLowerCase();
            $('#btnDownload').attr('href', fileUrl).show();
            if (ext === 'pdf') {
                $('#previewArea').html(`<iframe src="${fileUrl}" width="100%" height="400px" style="border:none;"></iframe>`);
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                $('#previewArea').html(`<img src="${fileUrl}" class="img-fluid rounded shadow-sm" style="max-height:400px;">`);
            } else {
                $('#previewArea').html('<div class="text-muted"><i class="fa-solid fa-file-arrow-down fa-3x mb-3"></i><br>Preview tidak tersedia.<br>Silakan download file.</div>');
            }
        } else {
             $('#previewArea').html('<div class="text-muted">Konten tidak ditemukan.</div>');
        }
    });
</script>
<?php
require_once "../config.php"; // Sesuaikan path

// --- Fungsi Helper (Baru) ---
// Fungsi ini akan kita gunakan di HTML untuk menampilkan nama pengirim
function getAuthorNameForAdmin($post) {
    // Admin bisa lihat nama asli
    $nama_pengirim = $post['author_mahasiswa'] ?? $post['author_dosen'] ?? $post['author_username'] ?? 'N/A';
    $displayName = htmlspecialchars($nama_pengirim);
    
    // Admin juga diberi tahu status anonimnya
    if ($post['is_anonim'] == 1) {
        $displayName .= ' <span class="badge bg-secondary ms-1">Anonim</span>';
    }
    return $displayName;
}
// --- End Helper ---


try {
    // Query JOIN (ini sudah benar, tidak diubah)
    $stmt = $pdo->query("
        SELECT 
            ks.id, 
            ks.judul, 
            ks.isi, -- (Tambahkan 'isi' untuk snippet)
            ks.tipe, 
            ks.is_anonim,
            ks.created_at,
            u.username AS author_username,
            m.nama_lengkap AS author_mahasiswa,
            d_author.nama_lengkap AS author_dosen,
            d_target.nama_lengkap AS target_dosen_nama
        FROM 
            kritik_saran AS ks
        LEFT JOIN 
            users AS u ON ks.user_id = u.id
        LEFT JOIN 
            mahasiswa AS m ON u.id = m.user_id
        LEFT JOIN 
            dosen AS d_author ON u.id = d_author.user_id
        LEFT JOIN 
            dosen AS d_target ON ks.target_dosen_id = d_target.id
        WHERE 
            ks.parent_id IS NULL
        ORDER BY
            ks.created_at DESC
    ");
    $postRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $postRows = [];
}
?>

<!--begin::App Main-->
<main class="app-main">
    <!-- ... (Header Breadcrumb tetap sama) ... -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Manajemen Forum & Kritik</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Forum & Kritik</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!--begin::Card-->
                    <div class="card">
                        <!-- ... (Card Header & Search Bar tetap sama) ... -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-2 mb-md-0">Data Postingan Utama</h3>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <input type="text" id="searchKritik" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari judul, isi, pengirim..." autocomplete="off">
                                        <div id="suggestionBoxKritik" 
                                             class="list-group position-absolute w-100" 
                                             style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                    <a href="./?p=manage-kritik" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-kritik-admin" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-plus"></i> Buat Postingan
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ======================================================== -->
                        <!-- === BAGIAN INI DIUBAH TOTAL DARI <table> KE <CARD> === -->
                        <!-- ======================================================== -->
                        <div class="card-body"> <!-- (dihapus p-0) -->
                            <?php if (empty($postRows)): ?>
                                <div class="alert alert-info text-center">
                                    Belum ada data postingan.
                                </div>
                            <?php else: ?>
                                <?php foreach ($postRows as $p): ?>
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">
                                                <?= htmlspecialchars($p['judul'] ?? 'Tanpa Judul'); ?>
                                            </h5>
                                            <?php if ($p['tipe'] == 'Publik'): ?>
                                                <span class="badge bg-success">Publik (Forum)</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Personal (Kritik)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <!-- Tampilkan cuplikan isi -->
                                            <p class="card-text fst-italic">
                                                "<?= nl2br(htmlspecialchars(substr($p['isi'], 0, 200))); ?><?php if (strlen($p['isi']) > 200) echo '...'; ?>"
                                            </p>
                                            
                                            <?php if ($p['tipe'] == 'Personal'): ?>
                                                <p class="mb-0"><strong>Target:</strong> <?= htmlspecialchars($p['target_dosen_nama'] ?? 'N/A'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                                            <div>
                                                <!-- Fitur yang kamu minta: Tampilkan nama asli -->
                                                <strong>Pengirim:</strong> <?= getAuthorNameForAdmin($p); ?>
                                                <br>
                                                <small><?= htmlspecialchars(date('d M Y, H:i', strtotime($p['created_at']))); ?></small>
                                            </div>
                                            <!-- Tombol Opsi Admin -->
                                            <div class='btn-group btn-group-sm' role='group'>
                                                <a href='./?p=detail-kritik-admin&id=<?= $p["id"]; ?>' class='btn btn-info text-white' title="Lihat Detail & Balasan">
                                                    <i class='fas fa-eye me-1'></i> Detail
                                                </a>
                                                <a href='./?p=edit-kritik-admin&id=<?= $p["id"]; ?>' class='btn btn-warning text-white' title="Edit Postingan Utama">
                                                    <i class='fas fa-edit me-1'></i> Edit
                                                </a>
                                                <a href='./?p=hapus-kritik&id=<?= $p["id"]; ?>' 
                                                   class='btn btn-danger btn-sm' 
                                                   onclick="return confirm('⚠️ Yakin mau hapus postingan ini? SEMUA BALASAN di dalamnya akan ikut terhapus permanen.');"
                                                   title="Hapus Thread">
                                                    <i class='fas fa-trash me-1'></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <!-- ======================================================== -->
                        <!-- === AKHIR PERUBAHAN === -->
                        <!-- ======================================================== -->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<!-- Script AJAX -->
<!-- Script AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fungsi Keyup untuk sugesti (TETAP SAMA)
    $('#searchKritik').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_kritik-admin.php',
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    $('#suggestionBoxKritik').html(data).show();
                },
                error: function(xhr) { console.log('Error:', xhr.responseText); }
            });
        } else {
            $('#suggestionBoxKritik').hide();
        }
    });

    // Sembunyikan jika klik di luar (TETAP SAMA)
    $(document).click(function(e) {
        if (!$(e.target).closest('#searchKritik, #suggestionBoxKritik').length) {
            $('#suggestionBoxKritik').hide();
        }
    });

    // =================================================
    // === FUNGSI KLIK SUGESTI (INI YANG DIPERBAIKI) ===
    // =================================================
    $(document).on('click', '#suggestionBoxKritik .suggestion-item', function(e) {
        e.preventDefault();
        
        // 1. Ambil data dari tombol yang diklik
        const kritikId = $(this).attr('data-id');
        const displayText = $(this).text();
        
        // 2. Set input search dan sembunyikan box
        $('#searchKritik').val(displayText);
        $('#suggestionBoxKritik').hide();

        // 3. Lakukan AJAX Call KEDUA untuk mengambil detail JSON
        $.ajax({
            url: 'ajax/search_kritik-admin.php', // Panggil file yang sama
            method: 'POST',
            data: { id: kritikId }, // Kirim ID untuk dapat detail JSON
            dataType: 'json',
            success: function(res) {
                // 4. Cari 'card-body' utama yang membungkus semua postingan
                // Kita cari .card-body yang BUKAN .card-header
                var cardContainer = $('main.app-main .card > .card-body').first();

                if (res && res.id) {
                    // 5. Buat HTML Card baru persis seperti di PHP
                    
                    // Logika Tipe
                    const tipe = res.tipe === 'Publik' 
                        ? `<span class="badge bg-success">Publik (Forum)</span>` 
                        : `<span class="badge bg-warning text-dark">Personal (Kritik)</span>`;
                    
                    // Logika Nama Pengirim (Admin lihat semua)
                    let pengirim = res.author_mahasiswa || res.author_dosen || res.author_username || 'N/A';
                    if(res.is_anonim == 1) pengirim += ' <span class="badge bg-secondary ms-1">Anonim</span>';

                    // Logika Target Dosen
                    const target = res.tipe === 'Personal' ? `<p class="mb-0"><strong>Target:</strong> ${res.target_dosen_nama || 'N/A'}</p>` : '';
                    
                    // Logika Tanggal
                    const tanggal = new Date(res.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                    const postHtml = `
                        <div class="card shadow-sm mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    ${res.judul || 'Tanpa Judul'}
                                </h5>
                                ${tipe}
                            </div>
                            <div class="card-body">
                                <p class="card-text fst-italic">
                                    "${res.isi ? nl2br(res.isi) : ''}"
                                </p>
                                ${target}
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Pengirim:</strong> ${pengirim}
                                    <br>
                                    <small>${tanggal}</small>
                                </div>
                                <div class='btn-group btn-group-sm' role='group'>
                                    <a href='./?p=detail-kritik-admin&id=${res.id}' class='btn btn-info text-white' title="Lihat Detail & Balasan">
                                        <i class='fas fa-eye me-1'></i> Detail
                                    </a>
                                    <a href='./?p=edit-kritik-admin&id=${res.id}' class='btn btn-warning text-white' title="Edit Postingan Utama">
                                        <i class='fas fa-edit me-1'></i> Edit
                                    </a>
                                    <a href='./?p=hapus-kritik&id=${res.id}' 
                                       class='btn btn-danger btn-sm' 
                                       onclick="return confirm('⚠️ Yakin mau hapus postingan ini?');"
                                       title="Hapus Thread">
                                        <i class='fas fa-trash me-1'></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // 6. Ganti isi 'cardContainer' dengan HANYA 1 card yang dicari
                    cardContainer.html(postHtml);

                } else {
                    // Jika tidak ketemu
                    cardContainer.html('<div class="alert alert-warning text-center">Data tidak ditemukan.</div>');
                }
            },
            error: function(xhr) { 
                console.error('AJAX Error:', xhr.responseText); 
                $('main.app-main .card > .card-body').first().html('<div class="alert alert-danger">Gagal memuat data.</div>');
            }
        });
    });

    // Fungsi untuk ubah nl2br(PHP) ke <br> (JS)
    function nl2br(str) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
    }
});
</script>
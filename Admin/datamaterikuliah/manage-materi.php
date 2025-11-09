<?php
require_once "../config.php"; // Sesuaikan path

try {
    // Query JOIN kompleks untuk mengambil SEMUA materi dan info relasinya
    $stmt = $pdo->query("
        SELECT 
            m.id, 
            m.judul, 
            m.tipe_materi, 
            m.created_at,
            mk.nama_mk,
            d.nama_lengkap AS nama_dosen,
            k.kelas, 
            k.angkatan
        FROM 
            materi_kuliah AS m
        LEFT JOIN 
            jadwal_kuliah AS jk ON m.jadwal_kuliah_id = jk.id
        LEFT JOIN 
            mata_kuliah AS mk ON jk.mata_kuliah_id = mk.id
        LEFT JOIN 
            dosen AS d ON jk.dosen_id = d.id
        LEFT JOIN 
            kelas AS k ON jk.kelas_id = k.id
        ORDER BY
            m.created_at DESC
    ");
    $materiRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $materiRows = []; // Kosongkan jika error
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Manajemen Materi Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Materi</li>
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
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-2 mb-md-0">Seluruh Materi Kuliah</h3>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <input type="text" id="searchMateri" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari judul, matkul, dosen..." autocomplete="off">
                                        <div id="suggestionBoxMateri" 
                                             class="list-group position-absolute w-100" 
                                             style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                                    </div>

                                    <a href="./?p=manage-materi" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-materi" class="btn btn-success btn-sm ms-2"> 
                                        <i class="fas fa-plus"></i> Tambah Materi
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th style="width: 5%;">No</th>
                                            <th style="width: 25%;">Judul Materi</th>
                                            <th style="width: 10%;">Tipe</th>
                                            <th style="width: 20%;">Mata Kuliah</th>
                                            <th style="width: 20%;">Dosen Pengupload</th>
                                            <th style="width: 10%;">Kelas</th>
                                            <th style="width: 10%;">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($materiRows)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Belum ada data materi.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $n = 1; ?>
                                            <?php foreach ($materiRows as $m): ?>
                                                <tr class='text-center'>
                                                    <td><?= $n++; ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($m['judul']); ?></td>
                                                    <td>
                                                        <?php if ($m['tipe_materi'] == 'File'): ?>
                                                            <span class="badge bg-primary">File</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Link</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class='text-start'><?= htmlspecialchars($m['nama_mk'] ?? 'N/A'); ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($m['nama_dosen'] ?? 'N/A'); ?></td>
                                                    <td><?= htmlspecialchars(($m['kelas'] ?? 'N/A') . ' (' . ($m['angkatan'] ?? '-') . ')'); ?></td>
                                                    <td>
                                                        <div class='btn-group btn-group-sm' role='group'>
                                                            <a href='./?p=detail-materi&id=<?= $m["id"]; ?>' class='btn btn-info text-white'>
                                                                <i class='fas fa-eye'></i> Detail
                                                            </a>
                                                            <a href='./?p=edit-materi&id=<?= $m["id"]; ?>' class='btn btn-warning text-white'>
                                                                <i class='fas fa-edit'></i> Edit
                                                            </a>
                                                            <a href='./?p=hapus-materi&id=<?= $m["id"]; ?>' 
                                                               class='btn btn-danger btn-sm' 
                                                               onclick="return confirm('⚠️ Yakin mau hapus materi ini? File fisik (jika ada) juga akan terhapus.');">
                                                                <i class='fas fa-trash'></i> Hapus
                                                            </a>
                                                            </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ... (AJAX keyup dan click luar - TETAP SAMA) ...
    $('#searchMateri').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_materi-admin.php',
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    $('#suggestionBoxMateri').html(data).show();
                },
                error: function(xhr) { console.log('Error:', xhr.responseText); }
            });
        } else {
            $('#suggestionBoxMateri').hide();
        }
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#searchMateri, #suggestionBoxMateri').length) {
            $('#suggestionBoxMateri').hide();
        }
    });

    // ... (AJAX klik suggestion) ...
    $(document).on('click', '#suggestionBoxMateri .suggestion-item', function(e) {
        e.preventDefault();
        const materiId = $(this).attr('data-id');
        const displayText = $(this).text();
        
        $('#searchMateri').val(displayText);
        $('#suggestionBoxMateri').hide();

        $.ajax({
            url: 'ajax/search_materi-admin.php',
            method: 'POST',
            data: { id: materiId }, 
            dataType: 'json',
            success: function(res) {
                if (res && res.id) {
                    const tipe = res.tipe_materi === 'File' 
                        ? `<span class="badge bg-primary">File</span>` 
                        : `<span class="badge bg-danger">Link</span>`;
                    const kelas = (res.kelas ? res.kelas : 'N/A') + ' (' + (res.angkatan ? res.angkatan : '-') + ')';
                    
                    // ===================== PERBAIKAN DI SINI (JAVASCRIPT) =====================
                    const row = `
                        <tr class="text-center">
                            <td>1</td>
                            <td class="text-start">${res.judul}</td>
                            <td>${tipe}</td>
                            <td class="text-start">${res.nama_mk ?? 'N/A'}</td>
                            <td class="text-start">${res.nama_dosen ?? 'N/A'}</td>
                            <td>${kelas}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detail-materi&id=${res.id}' class='btn btn-info text-white'>
                                        <i class='fas fa-eye'></i> Detail
                                    </a>
                                    <a href='?p=edit-materi&id=${res.id}' class='btn btn-warning text-white'>
                                        <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <a href='?p=hapus-materi&id=${res.id}' class='btn btn-danger' onclick="return confirm('Yakin hapus ini?')">
                                        <i class='fas fa-trash'></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    // ================= END PERBAIKAN =================
                    
                    $('table tbody').html(row);
                } else {
                    $('table tbody').html('<tr><td colspan="7" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
        });
    });
});
</script>
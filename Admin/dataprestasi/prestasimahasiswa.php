<?php
require_once "../config.php"; // Sesuaikan path

try {
    // Query JOIN untuk mengambil data prestasi + nama mahasiswa + nama penginput
    $stmt = $pdo->query("
        SELECT 
            p.id, 
            p.judul_prestasi, 
            p.tingkat, 
            p.tanggal_diraih,
            m.nama_lengkap AS nama_mahasiswa,
            u.username AS nama_penginput
        FROM 
            prestasi_mahasiswa AS p
        LEFT JOIN 
            mahasiswa AS m ON p.mahasiswa_id = m.id
        LEFT JOIN 
            users AS u ON p.input_by_user_id = u.id
        ORDER BY
            p.tanggal_diraih DESC
    ");
    $prestasiRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $prestasiRows = []; // Kosongkan jika error
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Manajemen Prestasi</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Prestasi Mahasiswa</li>
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
                                <h3 class="card-title mb-2 mb-md-0">Data Prestasi Mahasiswa</h3>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <input type="text" id="searchPrestasi" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari judul, nama mahasiswa..." autocomplete="off">
                                        <div id="suggestionBoxPrestasi" 
                                             class="list-group position-absolute w-100" 
                                             style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                    <a href="./?p=manage-prestasi" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-prestasi" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-plus"></i> Tambah Prestasi
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
                                            <th style="width: 30%;">Judul Prestasi</th>
                                            <th style="width: 25%;">Nama Mahasiswa</th>
                                            <th style="width: 15%;">Tingkat</th>
                                            <th style="width: 15%;">Tanggal Diraih</th>
                                            <th style="width: 10%;">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($prestasiRows)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada data prestasi.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $n = 1; ?>
                                            <?php foreach ($prestasiRows as $p): ?>
                                                <tr class='text-center'>
                                                    <td><?= $n++; ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($p['judul_prestasi']); ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($p['nama_mahasiswa'] ?? 'N/A'); ?></td>
                                                    <td><span class="badge bg-info"><?= htmlspecialchars($p['tingkat']); ?></span></td>
                                                    <td><?= htmlspecialchars(date('d M Y', strtotime($p['tanggal_diraih']))); ?></td>
                                                    <td>
                                                        <div class='btn-group btn-group-sm' role='group'>
                                                            <a href='./?p=detail-prestasi&id=<?= $p["id"]; ?>' class='btn btn-info text-white'>
                                                                <i class='fas fa-eye'></i> Detail
                                                            </a>
                                                            <a href='./?p=edit-prestasi&id=<?= $p["id"]; ?>' class='btn btn-warning text-white'>
                                                                <i class='fas fa-edit'></i> Edit
                                                            </a>
                                                            <a href='./?p=hapus-prestasi&id=<?= $p["id"]; ?>' 
                                                               class='btn btn-danger btn-sm' 
                                                               onclick="return confirm('⚠️ Yakin mau hapus prestasi ini?');">
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
    $('#searchPrestasi').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_prestasi-admin.php',
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    $('#suggestionBoxPrestasi').html(data).show();
                },
                error: function(xhr) { console.log('Error:', xhr.responseText); }
            });
        } else {
            $('#suggestionBoxPrestasi').hide();
        }
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#searchPrestasi, #suggestionBoxPrestasi').length) {
            $('#suggestionBoxPrestasi').hide();
        }
    });

    $(document).on('click', '#suggestionBoxPrestasi .suggestion-item', function(e) {
        e.preventDefault();
        const prestasiId = $(this).attr('data-id');
        const displayText = $(this).text();
        
        $('#searchPrestasi').val(displayText);
        $('#suggestionBoxPrestasi').hide();

        $.ajax({
            url: 'ajax/search_prestasi-admin.php',
            method: 'POST',
            data: { id: prestasiId }, // Kirim ID Prestasi
            dataType: 'json',
            success: function(res) {
                if (res && res.id) {
                    const tanggal = new Date(res.tanggal_diraih).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    const row = `
                        <tr class="text-center">
                            <td>1</td>
                            <td class="text-start">${res.judul_prestasi}</td>
                            <td class="text-start">${res.nama_mahasiswa ?? 'N/A'}</td>
                            <td><span class="badge bg-info">${res.tingkat}</span></td>
                            <td>${tanggal}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detail-prestasi&id=${res.id}' class='btn btn-info text-white'><i class='fas fa-eye'></i> Detail</a>
                                    <a href='?p=edit-prestasi&id=${res.id}' class='btn btn-warning text-white'><i class='fas fa-edit'></i> Edit</a>
                                    <a href='?p=hapus-prestasi&id=${res.id}' class='btn btn-danger' onclick="return confirm('Yakin hapus ini?')"><i class='fas fa-trash'></i> Hapus</a>
                                </div>
                            </td>
                        </tr>`;
                    $('table tbody').html(row);
                } else {
                    $('table tbody').html('<tr><td colspan="6" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) { console.error('AJAX Error:', xhr.responseText); }
        });
    });
});
</script>
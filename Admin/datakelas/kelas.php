<?php
require_once "../config.php"; // Pastikan path ini benar

// Query ambil semua data kelas
$stmt = $pdo->query("SELECT * FROM kelas ORDER BY angkatan DESC, kelas ASC");
$kelasRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Data Kelas</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Kelas</li>
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
                                <h3 class="card-title mb-2 mb-md-0">Data Kelas</h3>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <input type="text" id="searchKelas" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari nama kelas..." autocomplete="off">
                                        <div id="suggestionBoxKelas" 
                                             class="list-group position-absolute w-100" 
                                             style="z-index: 1000; display: none;"></div>
                                    </div>

                                    <a href="./?p=kelas" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-kelas" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-plus"></i> Tambah Data
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th style="width: 10%;">No</th>
                                            <th style="width: 35%;">Nama Kelas</th>
                                            <th style="width: 30%;">Angkatan</th>
                                            <th style="width: 25%;">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($kelasRows)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Belum ada data kelas.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $n = 1; ?>
                                            <?php foreach ($kelasRows as $k): ?>
                                                <tr class='text-center'>
                                                    <td><?= $n++; ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($k['kelas']); ?></td>
                                                    <td><?= htmlspecialchars($k['angkatan']); ?></td>
                                                    <td>
                                                        <div class='btn-group btn-group-sm' role='group'>
                                                            <a href='./?p=detail-kelas&id=<?= $k["id"]; ?>' class='btn btn-info'>
                                                                <i class='fas fa-eye'></i> Detail
                                                            </a>
                                                            <a href='./?p=edit-kelas&id=<?= $k["id"]; ?>' class='btn btn-warning text-white'>
                                                                <i class='fas fa-edit'></i> Edit
                                                            </a>
                                                            <a href='./?p=hapus-kelas&id=<?= $k["id"]; ?>' 
                                                               class='btn btn-danger btn-sm' 
                                                               onclick="return confirm('⚠️ Yakin mau hapus data kelas ini?');">
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
    // Fungsi keyup untuk search suggestion
    $('#searchKelas').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_kelas.php', // Target file AJAX
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    $('#suggestionBoxKelas').html(data).show();
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });
        } else {
            $('#suggestionBoxKelas').hide();
        }
    });

    // Sembunyikan suggestion jika klik di luar
    $(document).click(function(e) {
        if (!$(e.target).closest('#searchKelas, #suggestionBoxKelas').length) {
            $('#suggestionBoxKelas').hide();
        }
    });

    // Fungsi klik pada suggestion item
    $(document).on('click', '#suggestionBoxKelas .suggestion-item', function(e) {
        e.preventDefault();
        const nama = $(this).attr('data-name') || $(this).text().trim();
        $('#searchKelas').val(nama);
        $('#suggestionBoxKelas').hide();

        // AJAX untuk ambil detail dan update tabel
        $.ajax({
            url: 'ajax/search_kelas.php',
            method: 'POST',
            data: { nama: nama }, // Kirim 'nama' (nama kelas) untuk dapat detail
            dataType: 'json',
            success: function(res) {
                if (res && res.kelas) {
                    const row = `
                        <tr class="text-center">
                            <td>1</td>
                            <td class="text-start">${res.kelas}</td>
                            <td>${res.angkatan ?? 'N/A'}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detail-kelas&id=${res.id}' class='btn btn-info'>
                                        <i class='fas fa-eye'></i> Detail
                                    </a>
                                    <a href='?p=edit-kelas&id=${res.id}' class='btn btn-warning text-white'>
                                        <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <a href='?p=hapus-kelas&id=${res.id}' class='btn btn-danger'
                                       onclick="return confirm('Yakin mau hapus data ini?')">
                                        <i class='fas fa-trash'></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    $('table tbody').html(row); // Ganti isi tabel
                } else {
                    $('table tbody').html('<tr><td colspan="4" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
});
</script>
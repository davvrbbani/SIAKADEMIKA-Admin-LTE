<?php
require_once "../config.php"; // Pastikan path ini benar

try {
    // Query JOIN untuk menggabungkan 4 tabel
    $stmt = $pdo->query("
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
        ORDER BY
            FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), 
            jk.jam_mulai ASC
    ");
    $jadwalRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $jadwalRows = []; // Kosongkan jika error
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Data Jadwal Kuliah</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Jadwal Kuliah</li>
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
                                <h3 class="card-title mb-2 mb-md-0">Data Jadwal Kuliah</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        <input type="text" id="searchJadwal" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari matkul, dosen, hari..." autocomplete="off">
                                        <div id="suggestionBoxJadwal" 
                                             class="list-group position-absolute w-100" 
                                             style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                    <a href="./?p=jadwal-kuliah" class="btn btn-primary btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-jadwal" class="btn btn-success btn-sm ms-2">
                                        <i class="fas fa-plus"></i> Tambah Jadwal
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
                                            <th style="width: 10%;">Hari</th>
                                            <th style="width: 15%;">Waktu</th>
                                            <th style="width: 10%;">Ruangan</th>
                                            <th style="width: 20%;">Mata Kuliah</th>
                                            <th style="width: 20%;">Dosen</th>
                                            <th style="width: 10%;">Kelas</th>
                                            <th style="width: 10%;">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($jadwalRows)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Belum ada data jadwal kuliah.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $n = 1; ?>
                                            <?php foreach ($jadwalRows as $j): ?>
                                                <tr class='text-center'>
                                                    <td><?= $n++; ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($j['hari']); ?></td>
                                                    <td><?= htmlspecialchars(substr($j['jam_mulai'], 0, 5) . ' - ' . substr($j['jam_selesai'], 0, 5)); ?></td>
                                                    <td><?= htmlspecialchars($j['ruangan']); ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($j['nama_mk'] ?? 'N/A'); ?></td>
                                                    <td class='text-start'><?= htmlspecialchars($j['nama_dosen'] ?? 'N/A'); ?></td>
                                                    <td><?= htmlspecialchars(($j['kelas'] ?? 'N/A') . ' (' . ($j['angkatan'] ?? '-') . ')'); ?></td>
                                                    <td>
                                                        <div class='btn-group btn-group-sm' role='group'>
                                                            <a href='./?p=detail-jadwal&id=<?= $j["id"]; ?>' class='btn btn-info text-white'>
                                                                <i class='fas fa-eye'></i> Detail
                                                            </a>
                                                            <a href='./?p=edit-jadwal&id=<?= $j["id"]; ?>' class='btn btn-warning text-white'>
                                                                <i class='fas fa-edit'></i> Edit
                                                            </a>
                                                            <a href='./?p=hapus-jadwal&id=<?= $j["id"]; ?>' 
                                                               class='btn btn-danger btn-sm' 
                                                               onclick="return confirm('⚠️ Yakin mau hapus jadwal ini?');">
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
    // ... (AJAX untuk search keyup tetap sama) ...
    $('#searchJadwal').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_jadwal.php',
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    $('#suggestionBoxJadwal').html(data).show();
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });
        } else {
            $('#suggestionBoxJadwal').hide();
        }
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#searchJadwal, #suggestionBoxJadwal').length) {
            $('#suggestionBoxJadwal').hide();
        }
    });

    // ... (AJAX untuk klik suggestion) ...
    $(document).on('click', '#suggestionBoxJadwal .suggestion-item', function(e) {
        e.preventDefault();
        const jadwalId = $(this).attr('data-id'); 
        const displayText = $(this).text();
        
        $('#searchJadwal').val(displayText); 
        $('#suggestionBoxJadwal').hide();

        $.ajax({
            url: 'ajax/search_jadwal.php',
            method: 'POST',
            data: { id: jadwalId }, 
            dataType: 'json',
            success: function(res) {
                if (res && res.id) {
                    const waktu = (res.jam_mulai ? res.jam_mulai.substring(0, 5) : '') + ' - ' + (res.jam_selesai ? res.jam_selesai.substring(0, 5) : '');
                    const kelas = (res.kelas ? res.kelas : 'N/A') + ' (' + (res.angkatan ? res.angkatan : '-') + ')';
                    
                    // ===================== PERBAIKAN DI SINI (JAVASCRIPT) =====================
                    const row = `
                        <tr class="text-center">
                            <td>1</td>
                            <td class="text-start">${res.hari ?? ''}</td>
                            <td>${waktu}</td>
                            <td>${res.ruangan ?? ''}</td>
                            <td class="text-start">${res.nama_mk ?? 'N/A'}</td>
                            <td class="text-start">${res.nama_dosen ?? 'N/A'}</td>
                            <td>${kelas}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detail-jadwal&id=${res.id}' class='btn btn-info text-white'>
                                        <i class='fas fa-eye'></i> Detail
                                    </a>
                                    <a href='?p=edit-jadwal&id=${res.id}' class='btn btn-warning text-white'>
                                        <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <a href='?p=hapus-jadwal&id=${res.id}' class='btn btn-danger'
                                       onclick="return confirm('Yakin mau hapus data ini?')">
                                        <i class='fas fa-trash'></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    // ================= END PERBAIKAN =================
                    
                    $('table tbody').html(row); // Ganti isi tabel
                } else {
                    $('table tbody').html('<tr><td colspan="8" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
});
</script>
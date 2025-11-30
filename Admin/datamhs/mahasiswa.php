<?php
require_once "../config.php";

// --- 1. AMBIL DATA KELAS (Untuk Dropdown Filter) ---
try {
    $stmtKelas = $pdo->query("SELECT * FROM kelas ORDER BY kelas ASC");
    $kelasList = $stmtKelas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal ambil kelas: " . $e->getMessage());
}

// --- 2. QUERY UTAMA MAHASISWA (Backend yang Diperbaiki) ---
// Menggunakan LEFT JOIN agar Nama Kelas langsung muncul tanpa error array
$filter_kelas = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

$sql = "
    SELECT 
        m.id,
        m.nama_lengkap,
        m.NIM,
        m.semester,
        k.kelas AS nama_kelas
    FROM mahasiswa m
    LEFT JOIN kelas k ON m.kelas_id = k.id
    WHERE 1=1 
";

// Tambahkan filter jika ada kelas yg dipilih
if (!empty($filter_kelas)) {
    $sql .= " AND m.kelas_id = :kelas_id";
}

$sql .= " ORDER BY m.nama_lengkap ASC";

try {
    $stmt = $pdo->prepare($sql);
    if (!empty($filter_kelas)) {
        $stmt->bindValue(':kelas_id', $filter_kelas);
    }
    $stmt->execute();
    $mahasiswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Data Mahasiswa</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Mahasiswa</li>
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
                                <h3 class="card-title mb-2 mb-md-0">Data Mahasiswa</h3>
                                
                                <div class="d-flex align-items-center gap-2">
                                    
                                    <form method="GET" action="" class="d-flex align-items-center">
                                        <input type="hidden" name="p" value="mahasiswa">
                                        <select class="form-select form-select-sm me-2" name="kelas_id" onchange="this.form.submit()" style="width: 150px;">
                                            <option value="">-- Semua Kelas --</option>
                                            <?php foreach ($kelasList as $k): ?>
                                                <option value="<?= $k['id'] ?>" <?= ($filter_kelas == $k['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($k['kelas']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <div class="position-relative">
                                        <input type="text" id="searchMahasiswa" 
                                               class="form-control form-control-sm me-2" 
                                               placeholder="Cari nama mahasiswa..." autocomplete="off" style="width: 200px;">
                                        
                                        <div id="suggestionBox" 
                                             class="list-group position-absolute w-100 shadow" 
                                             style="z-index: 1000; display: none;"></div>
                                    </div>

                                    <a href="./?p=mahasiswa" class="btn btn-primary btn-sm ms-1">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="./?p=add-mahasiswa" class="btn btn-success btn-sm ms-1">
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
                                            <th style="width: 5%;">No</th>
                                            <th style="width: 30%;">Nama</th>
                                            <th style="width: 10%;">Semester</th>
                                            <th style="width: 20%;">NIM</th>
                                            <th style="width: 15%;">Kelas</th>
                                            <th style="width: 20%;">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($mahasiswaList)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Data tidak ditemukan.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $n = 1; foreach ($mahasiswaList as $mhs): ?>
                                                <tr class="text-center align-middle">
                                                    <td><?= $n++; ?></td>
                                                    <td class="text-start fw-bold"><?= htmlspecialchars($mhs['nama_lengkap']) ?></td>
                                                    <td><?= htmlspecialchars($mhs['semester']) ?></td>
                                                    <td><?= htmlspecialchars($mhs['NIM']) ?></td>
                                                    <td>
                                                        <?php if(!empty($mhs['nama_kelas'])): ?>
                                                            <?= htmlspecialchars($mhs['nama_kelas']) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted small">No Class</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="./?p=detail-mahasiswa&id=<?= $mhs['id'] ?>" class="btn btn-info text-white">
                                                                Detail
                                                            </a>
                                                            <a href="./?p=edit-mahasiswa&id=<?= $mhs['id'] ?>" class="btn btn-warning text-white">
                                                                Edit
                                                            </a>
                                                            <a href="./?p=hapus-mahasiswa&id=<?= $mhs['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin mau hapus data ini?')">
                                                                Hapus
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
                        <div class="card-footer clearfix">
                             <small class="text-muted">Total: <?= count($mahasiswaList) ?> Mahasiswa</small>
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
    // 1. Event Ketik di Search Box (Menampilkan Sugesti)
    $('#searchMahasiswa').on('keyup', function() {
        let query = $(this).val().trim();
        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_mahasiswa.php', // Pastikan file ini ada & jalurnya benar!
                method: 'POST',
                data: {query: query},
                success: function(data) {
                    $('#suggestionBox').html(data).show();
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });
        } else {
            $('#suggestionBox').hide();
        }
    });

    // 2. Sembunyikan Sugesti jika klik di luar
    $(document).click(function(e) {
        if (!$(e.target).closest('#searchMahasiswa, #suggestionBox').length) {
            $('#suggestionBox').hide();
        }
    });

    // 3. Event Klik Item Sugesti (Mengganti Isi Tabel)
    $(document).on('click', '.suggestion-item', function(e) {
        e.preventDefault();

        const nama = $(this).attr('data-name') || $(this).text().trim();
        // console.log('Klik nama:', nama); 

        $('#searchMahasiswa').val(nama);
        $('#suggestionBox').hide();

        // AJAX panggil detail mahasiswa untuk mengganti isi tabel
        $.ajax({
            url: 'ajax/search_mahasiswa.php',
            method: 'POST',
            data: { nama: nama }, // Kirim nama lengkap
            dataType: 'json', // Harap pastikan PHP mengembalikan JSON
            success: function(res) {
                // console.log('Respon:', res); 

                if (res && res.nama_lengkap) {
                    // Render ulang baris tabel (Single Row Result)
                    // Tombol OPSI di sini disamakan dengan tampilan default (Detail, Edit, Hapus)
                    const row = `
                        <tr class="text-center align-middle">
                            <td>1</td>
                            <td class="text-start fw-bold">${res.nama_lengkap}</td>
                            <td>${res.semester ?? 'N/A'}</td>
                            <td>${res.NIM ?? ''}</td>
                            <td>
                                ${res.nama_kelas ?? '<span class="text-muted small">No Class</span>'}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detail-mahasiswa&id=${res.id}' class='btn btn-info text-white'>
                                        Detail
                                    </a>
                                    <a href='?p=edit-mahasiswa&id=${res.id}' class='btn btn-warning text-white'>
                                        Edit
                                    </a>
                                    <a href='?p=hapus-mahasiswa&id=${res.id}' class='btn btn-danger'
                                       onclick="return confirm('Yakin mau hapus data ini?')">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    $('table tbody').html(row);
                } else {
                    $('table tbody').html('<tr><td colspan="6" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
});
</script>
<?php
require_once "../config.php";

// Ambil data user role dosen
$usersStmt = $pdo->query("SELECT * FROM `users` WHERE role = 'dosen'");
$dosenStmt = $pdo->query("SELECT * FROM `dosen`");

// Fetch hasil
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
$dosenRows = $dosenStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">Data Dosen</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Dosen</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->

  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <!--begin::Card-->
          <div class="card">
            <!-- Card Header -->
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-2 mb-md-0">Data Dosen</h3>

                <div class="d-flex align-items-center gap-2">
                  <!-- Form Search -->
                  <div class="position-relative">
                    <input type="text" id="searchDosen"
                          class="form-control form-control-sm me-2"
                          placeholder="Cari nama dosen..." autocomplete="off">
                    <div id="suggestionBoxDosen"
                        class="list-group position-absolute w-100"
                        style="z-index: 1000; display: none;"></div>
                  </div>

                  <a href="./?p=dosen" class="btn btn-primary btn-sm ms-2">
                    <i class="bi bi-arrow-clockwise"></i></a>
                  <a href="./?p=add-dosen" class="btn btn-success btn-sm ms-2">
                    <i class="fas fa-plus"></i> Tambah Data
                  </a>
                </div>
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-light text-center">
                    <tr>
                      <th style="width:5%;">No</th>
                      <th style="width:40%;">Nama</th>
                      <th style="width:25%;">NIDN</th>
                      <th style="width:30%;">Opsi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $n = 1;
                    $dosenMap = [];
                    foreach ($dosenRows as $ds) {
                        $key = $ds['user_id'] ?? $ds['id'] ?? null;
                        if ($key) $dosenMap[$key] = $ds;
                    }

                    // Filter pencarian
                    $keyword = isset($_GET['keyword']) ? strtolower(trim($_GET['keyword'])) : '';

                    foreach ($users as $d) {
                        $userId = $d['id'] ?? null;
                        $mapRow = $userId && isset($dosenMap[$userId]) ? $dosenMap[$userId] : null;

                        $nama = $mapRow['nama_lengkap'] ?? 'N/A';
                        $nidn = $mapRow['nidn'] ?? '-';

                        if ($keyword && strpos(strtolower($nama), $keyword) === false) continue;

                        echo "
                        <tr class='text-center'>
                          <td>{$n}</td>
                          <td class='text-start'>".htmlspecialchars($nama)."</td>
                          <td>".htmlspecialchars($nidn)."</td>
                          <td>
                            <div class='btn-group btn-group-sm' role='group'>
                              <a href='./?p=detaildsn&id={$mapRow["id"]}' class='btn btn-info'>
                                <i class='fas fa-eye'></i> Detail
                              </a>
                              <a href='./?p=editdsn&id={$mapRow["id"]}' class='btn btn-warning text-white'>
                                <i class='fas fa-edit'></i> Edit
                              </a>
                              <a href='./?p=hapusdsn&id={$mapRow["id"]}' class='btn btn-danger'
                                onclick=\"return confirm('⚠️ Yakin mau hapus data dosen ini? Data akan hilang permanen.');\">
                                <i class='fas fa-trash'></i> Hapus
                              </a>
                            </div>
                          </td>
                        </tr>";
                        $n++;
                    }
                    if ($n === 1) echo "<tr><td colspan='4' class='text-center text-muted'>Tidak ada data ditemukan</td></tr>";
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!--end::Card-->
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content-->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // 1. Menampilkan suggestion/saran saat mengetik
    $('#searchDosen').on('keyup', function() {
        let query = $(this).val().trim();
        let suggestionBox = $('#suggestionBoxDosen'); // Cache selector

        if (query.length > 1) {
            $.ajax({
                url: 'ajax/search_dosen.php', // URL untuk search dosen
                method: 'POST',
                data: { query: query },
                success: function(data) {
                    suggestionBox.html(data).show();
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                    suggestionBox.hide();
                }
            });
        } else {
            suggestionBox.hide();
        }
    });

    // 2. Menangani klik pada item suggestion (Ini bagian yang di-upgrade)
    $(document).on('click', '#suggestionBoxDosen .suggestion-item', function(e) {
        e.preventDefault(); // Mencegah aksi default link '#'

        // Ambil nama dari data-name (lebih aman) atau teks
        const nama = $(this).attr('data-name') || $(this).text().trim();
        
        $('#searchDosen').val(nama); // Isi input search
        $('#suggestionBoxDosen').hide(); // Sembunyikan box suggestion

        // 3. AJAX kedua: Ambil data detail dosen dan update tabel
        $.ajax({
            url: 'ajax/search_dosen.php', // Panggil file yang sama, tapi kirim 'nama'
            method: 'POST',
            data: { nama: nama }, // Kirim nama lengkap untuk dapat detail
            dataType: 'json', // Harapkan balasan JSON
            success: function(res) {
                console.log('Respon Dosen:', res); // Debug

                if (res && res.nama_lengkap) {
                    // Buat baris tabel baru berdasarkan data dosen
                    // Sesuaikan kolom (NIDN, Email)
                    const row = `
                        <tr class="text-center">
                            <td>1</td>
                            <td class="text-start">${res.nama_lengkap}</td>
                            <td>${res.nidn ?? ''}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href='?p=detaildsn&id=${res.id}' class='btn btn-info'>
                                        <i class='fas fa-eye'></i> Detail
                                    </a>
                                    <a href='?p=editdsn&id=${res.id}' class='btn btn-warning text-white'>
                                        <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <a href='?p=hapusdsn&id=${res.id}' class='btn btn-danger'
                                       onclick="return confirm('Yakin mau hapus data dosen ini?')">
                                        <i class='fas fa-trash'></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    // Ganti isi <tbody> tabel dengan data yang ditemukan
                    $('table tbody').html(row);
                } else {
                    // Tampilkan pesan jika data tidak ditemukan
                    // Pastikan colspan=5 (No, Nama, NIDN, Email, Aksi)
                    $('table tbody').html('<tr><td colspan="5" class="text-center text-muted">Data tidak ditemukan</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error (Get Detail):', xhr.responseText);
                $('table tbody').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
        });
    });

    // 4. Menyembunyikan suggestion box jika klik di luar area
    $(document).click(function(e) {
        // Cek jika target klik BUKAN #searchDosen atau #suggestionBoxDosen
        if (!$(e.target).closest('#searchDosen, #suggestionBoxDosen').length) {
            $('#suggestionBoxDosen').hide();
        }
    });

});
</script>
</main>
<!--end::App Main-->

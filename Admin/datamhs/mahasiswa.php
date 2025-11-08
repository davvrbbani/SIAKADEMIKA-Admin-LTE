<?php
require_once "../config.php";

// Query pakai PDO
$usersStmt = $pdo->query("SELECT * FROM `users` WHERE role = 'mahasiswa'");
$mahasiswaStmt = $pdo->query("SELECT * FROM `mahasiswa`");

// fetch arrays
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
$mahasiswaRows = $mahasiswaStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <div class="col-sm-6"><h3 class="mb-0">Data Mahasiswa</h3></div>
              <!--end::Col-->
              <!--begin::Col-->
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data Mahasiswa</li>
                </ol>
              </div>
              <!--end::Col-->
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <div class="col-12">
                <!--begin::Card-->
                <div class="card">
                  <!-- Card Header -->
                  <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                      <h3 class="card-title mb-2 mb-md-0">Data Mahasiswa</h3>
                      
                      <div class="d-flex align-items-center gap-2">
                        <!-- Form Search -->
                      <div class="position-relative">
                        <input type="text" id="searchMahasiswa" 
                              class="form-control form-control-sm me-2" 
                              placeholder="Cari nama mahasiswa..." autocomplete="off">
                        <div id="suggestionBox" 
                            class="list-group position-absolute w-100" 
                            style="z-index: 1000; display: none;"></div>
                      </div>

                        <a href="./?p=mahasiswa" class="btn btn-primary btn-sm ms-2">
                          <i class="bi bi-arrow-clockwise"></i></a>
                        <!-- Tombol Tambah -->
                        <a href="./?p=add-mahasiswa" class="btn btn-success btn-sm ms-2">
                          <i class="fas fa-plus"></i> Tambah Data
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- Card Body -->
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                          <tr class="text-center">
                            <th style="width: 5%;">No</th>
                            <th style="width: 30%;">Nama</th>
                            <th style="width: 20%;">Semester</th>
                            <th style="width: 20%;">NIM</th>
                            <th style="width: 25%;">Opsi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $n = 1;
                          $mahasiswaMap = [];
                          foreach ($mahasiswaRows as $mr) {
                              $key = $mr['user_id'] ?? $mr['id'] ?? null;
                              if ($key) $mahasiswaMap[$key] = $mr;
                          }

                          // Filter jika ada pencarian
                          $keyword = isset($_GET['keyword']) ? strtolower(trim($_GET['keyword'])) : '';

                          foreach ($users as $d) {
                              $userId = $d['id'] ?? null;
                              $mapRow = $userId && isset($mahasiswaMap[$userId]) ? $mahasiswaMap[$userId] : null;

                              $semester = $mapRow['semester'] ?? 'N/A';
                              $nama_lengkap = $d['nama_lengkap'] ?? ($mapRow['nama_lengkap'] ?? '');
                              $nim = $mapRow['NIM'] ?? '';

                              // filter by keyword
                              if ($keyword && strpos(strtolower($nama_lengkap), $keyword) === false) continue;

                              echo "
                              <tr class='text-center'>
                                <td>{$n}</td>
                                <td class='text-start'>".htmlspecialchars($nama_lengkap)."</td>
                                <td>".htmlspecialchars($semester)."</td>
                                <td>".htmlspecialchars($nim)."</td>
                                <td>
                                  <div class='btn-group btn-group-sm' role='group'>
                                    <a href='./?p=detail-mahasiswa&id={$mapRow["id"]}' class='btn btn-info'>
                                      <i class='fas fa-eye'></i> Detail
                                    </a>
                                    <a href='./?p=edit-mahasiswa&id={$mapRow["id"]}' class='btn btn-warning text-white'>
                                      <i class='fas fa-edit'></i> Edit
                                    </a>
                                    <a href='./?p=hapus-mahasiswa&id={$mapRow["id"]}' 
                                      class='btn btn-danger btn-sm' 
                                      onclick=\"return confirm('⚠️ Yakin mau hapus data mahasiswa ini? Data akan hilang permanen.');\">
                                      <i class='fas fa-trash'></i> Hapus
                                    </a>
                                  </div>
                                </td>
                              </tr>";
                              $n++;
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!--end::Card-->
              </div>
              <!--end::Col-->
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $('#searchMahasiswa').on('keyup', function() {
    let query = $(this).val().trim();
    if (query.length > 1) {
      $.ajax({
        url: 'ajax/search_mahasiswa.php', // sesuaikan path kalau perlu
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

  $(document).on('click', '.suggestion-item', function() {
    $('#searchMahasiswa').val($(this).text());
    $('#suggestionBox').hide();
  });

  $(document).click(function(e) {
    if (!$(e.target).closest('#searchMahasiswa, #suggestionBox').length) {
      $('#suggestionBox').hide();
    }
  
  });

$(document).on('click', '.suggestion-item', function(e) {
  e.preventDefault();

  const nama = $(this).attr('data-name') || $(this).text().trim();
  console.log('Klik nama:', nama); // DEBUG

  $('#searchMahasiswa').val(nama);
  $('#suggestionBox').hide();

  // AJAX panggil detail mahasiswa
  $.ajax({
    url: 'ajax/search_mahasiswa.php',
    method: 'POST',
    data: { nama: nama },
    dataType: 'json',
    success: function(res) {
      console.log('Respon:', res); // DEBUG

      if (res && res.nama_lengkap) {
        const row = `
          <tr class="text-center">
            <td>1</td>
            <td class="text-start">${res.nama_lengkap}</td>
            <td>${res.semester ?? 'N/A'}</td>
            <td>${res.NIM ?? ''}</td>
            <td>
              <div class="btn-group btn-group-sm" role="group">
                <a href='?p=detail-mahasiswa&id=${res.id}' class='btn btn-info'>
                  <i class='fas fa-eye'></i> Detail
                </a>
                <a href='?p=edit-mahasiswa&id=${res.id}' class='btn btn-warning text-white'>
                  <i class='fas fa-edit'></i> Edit
                </a>
                <a href='?p=hapus-mahasiswa&id=${res.id}' class='btn btn-danger'
                  onclick="return confirm('Yakin mau hapus data ini?')">
                  <i class='fas fa-trash'></i> Hapus
                </a>
              </div>
            </td>
          </tr>`;
        $('table tbody').html(row);
      } else {
        $('table tbody').html('<tr><td colspan="5" class="text-center text-muted">Data tidak ditemukan</td></tr>');
      }
    },
    error: function(xhr) {
      console.error('AJAX Error:', xhr.responseText);
    }
  });
});
});
</script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      </main>
      <!--end::App Main-->
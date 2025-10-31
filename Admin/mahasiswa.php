<?php
require_once "../config.php";

// Query pakai PDO
$stmt = $pdo->query("SELECT * FROM `users` WHERE role = 'mahasiswa'");
$data = $stmt->fetchAll();
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
              <div class="col-sm-6"><h3 class="mb-0">Dashboard Admin</h3></div>
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
                  <!--begin::Card Header-->
                  <div class="card-header">
                    <!--begin::Card Title-->
                    <h3 class="card-title">Data Mahasiswa</h3>
                    <!--end::Card Title-->
                    <div class="d-flex justify-content-end mb-3">
                    <a href="./?p=add-mahasiswa" button type="button" class="btn btn-success">Tambah Data</button></a>
                    </div>
                  <table class="table table-stripped table-hover">
                    <tr><th>No</th><th>ID</th><th>Nama</th><th>Email</th><th>Prodi</th><th>Tanggal</th><th>Option</th></tr>
                  <?php
                  $n = 1;
                  foreach ($data as $d) {
                      $prodi = match($d['prodi']) {
                          1 => 'INF',
                          2 => 'ARS',
                          default => 'Tidak Diketahui'
                      };
                      echo "<tr>
                              <td>{$n}</td>
                              <td>{$d['id']}</td>
                              <td>{$d['username']}</td>
                              <td>{$d['email']}</td>
                              <td>{$prodi}</td>
                              <td>{$d['created_at']}</td>
                              <td>
                                  <a href='#' class='btn btn-sm btn-info'>Detail</a>
                                  <a href='#' class='btn btn-sm btn-warning'>Edit</a>
                                  <a href='#' class='btn btn-sm btn-danger'>Hapus</a>
                              </td>
                          </tr>";
                      $n++;
                  }
                  ?>

                  </table>
                    <!--begin::Card Toolbar-->
                    <div class="card-tools">
                  <!--end::Card Footer-->
           <!--end::Card Footer-->
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
      </main>
      <!--end::App Main-->
<?php
require_once "../config.php";
$data=$konek->query("SELECT * FROM siakad");
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
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
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
                    <tr><th>No</th><th>NIM</th><th>Nama</th><th>Gender</th><th>Prodi</th><th>Tanggal</th><th>Option</th></tr>
                    <?php
                    $n = 0;
                    foreach ($data as $d) {
                      $n++;
                      if($d['prodi']==1){
                        $prodi="INF";
                      }elseif($d['prodi']==2){
                        $prodi="ARS";
                      }
                      else{
                        $prodi="Tidak Di Ketahui";
                      }
                      echo "<tr><td>$n</td><td>$d[nim]</td><td>$d[nama]</td><td>$d[gender]</td><td>$prodi</td><td>$d[W]</td>
                      <td>
                      <a href='#' class='btn btn-sm btn-info'>Detail</a>
                      <a href='#' class='btn btn-sm btn-warning'>Edit</a>
                      <a href='#' class='btn btn-sm btn-danger'>Hapus</a>
                      </td></tr>";
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
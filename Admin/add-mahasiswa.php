<?php
require_once "../config.php";

if (isset($_POST['simpan'])) {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $gender = $_POST['gender'];
    $addres = $_POST['addres'];
    $prodi = $_POST['prodi'];
    $W = $_POST['W'];

    try {
        // Gunakan PDO prepare statement
        $stmt = $pdo->prepare("INSERT INTO siakad (nim, nama, gender, addres, prodi, W)
                               VALUES (:nim, :nama, :gender, :addres, :prodi, :W)");
        $stmt->execute([
            ':nim' => $nim,
            ':nama' => $nama,
            ':gender' => $gender,
            ':addres' => $addres,
            ':prodi' => $prodi,
            ':W' => $W
        ]);

        echo "<script>alert('Data berhasil disimpan!'); window.location.href = './?p=mahasiswa';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menyimpan data: " . $e->getMessage() . "');</script>";
    }
}
?>
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
                  <li class="breadcrumb-item active" aria-current="page">Dashboard Admin</li>
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
                <div class="card-header">
                  Tambah Data Mahasiswa
                </div>
                <div class="card-body">
                  <table>
                  <form method ="POST" action="">
                    <div class="mb-3">
                      <label class="form-label">NIM</label>
                      <input type="text" class="form-control" name="nim" placeholder="Masukkan NIM" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nama</label>
                      <input type="text" class="form-control" name="nama" placeholder="Masukkan Nama Mahasiswa" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Gender</label>
                      <select class="form-select" name="gender" required>
                        <option value="">-- Pilih Gender --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Alamat</label>
                      <input type="text" class="form-control" name="addres" placeholder="Masukkan Alamat" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Program Studi</label>
                      <select class="form-select" name="prodi" required>
                        <option value="">-- Pilih Prodi --</option>
                        <option value="1">Informatika</option>
                        <option value="2">Arsitektur</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tanggal</label>
                      <input type="date" class="form-control" name="W" required>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                      <a href="./?p=mahasiswa" class="btn btn-secondary">Kembali</a>
                      <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </table>
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
      </main>
      <!--end::App Main-->
      
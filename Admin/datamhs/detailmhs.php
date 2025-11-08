<?php
require_once "../config.php";

// Ambil id mahasiswa dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID mahasiswa tidak valid.</div>";
    exit;
}

try {
    $stmt = $pdo->prepare("
SELECT 
        m.*, 
        k.kelas, 
        k.angkatan, 
        u.username, 
        u.email,
        u.role,
        u.created_at,  
        u.updated_at   
    FROM mahasiswa m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN kelas k ON m.kelas_id = k.id
    WHERE m.id = ?
    LIMIT 1
");
    $stmt->execute([$id]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mhs) {
        echo "<div class='alert alert-warning'>Data mahasiswa tidak ditemukan.</div>";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Query error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
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
                    <div class="card-header bg-primary text-white">
                      <h4 class="mb-0">Detail Mahasiswa</h4>
                    </div>
                    <div class="card-body">
                      <table class="table table-bordered">
                        <tr>
                          <th style="width: 30%;">Nama Lengkap</th>
                          <td><?= htmlspecialchars($mhs['nama_lengkap']) ?></td>
                        </tr>
                        <tr>
                          <th>NIM</th>
                          <td><?= htmlspecialchars($mhs['NIM']) ?></td>
                        </tr>
                        <tr>
                          <th>Semester</th>
                          <td><?= htmlspecialchars($mhs['semester']) ?></td>
                        </tr>
                        <tr>
                          <th>Kelas</th>
                          <td><?= htmlspecialchars($mhs['kelas'] . ' ' . $mhs['angkatan']) ?></td>
                        </tr>
                        <tr>
                          <th>Username</th>
                          <td><?= htmlspecialchars($mhs['username']) ?></td>
                        </tr>
                        <tr>
                          <th>Email</th>
                          <td><?= htmlspecialchars($mhs['email']) ?></td>
                        </tr>
                        <tr>
                          <th>Role</th>
                          <td><?= htmlspecialchars($mhs['role']) ?></td>
                        </tr>
                        <tr>
                          <th>Dibuat pada</th>
                          <td><?= htmlspecialchars($mhs['created_at']) ?></td>
                        </tr>
                        <tr>
                          <th>Terakhir diperbarui</th>
                          <td><?= htmlspecialchars($mhs['updated_at']) ?></td>
                        </tr>
                      </table>
                    </div>
                            <div class="card-footer text-end">
                                <a href="./?p=mahasiswa" class="btn btn-primary text-white">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <a href="./?p=edit-mahasiswa&id=<?= $mhs['id'] ?>" class="btn btn-warning text-white">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="./?p=hapus-mahasiswa&id=<?= $mhs['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('⚠️ Yakin mau hapus data dosen ini? Data akan hilang permanen.');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
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
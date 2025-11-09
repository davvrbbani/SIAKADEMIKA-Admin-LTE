<?php
// We will need the main config for the database connection ($pdo) and session start.
// The path '../config.php' assumes this file is in the Mahasiswa/ folder
// and config.php is in the root directory. Adjust if necessary.
require_once '../config.php';

// Include our new student identity logic.
// This will handle security checks and fetch the student's profile into $current_student.
require_once 'config/student_identity.php';

// Now, we can use $current_student['kelas_id'] to get the schedule.
$jadwal_list = []; // Initialize an empty array
if (isset($current_student['kelas_id'])) {
    try {
        // This is a secure prepared statement with JOINs to get all necessary data.
        $stmt = $pdo->prepare(
            "SELECT 
                jk.hari, 
                jk.jam_mulai, 
                jk.jam_selesai, 
                jk.ruangan,
                mk.nama_mk,
                mk.sks,
                d.nama_lengkap AS nama_dosen
             FROM jadwal_kuliah jk
             JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
             JOIN dosen d ON jk.dosen_id = d.id
             WHERE jk.kelas_id = :kelas_id
             ORDER BY FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jk.jam_mulai ASC"
        );
        $stmt->execute(['kelas_id' => $current_student['kelas_id']]);
        $jadwal_list = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Handle database errors gracefully
        die("Error: Tidak dapat mengambil jadwal kuliah: " . $e->getMessage());
    }
}
?>
<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Jadwal Mata Kuliah</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Jadwal Kuliah</li>
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
                        <div class="card-header">
                            <h3 class="card-title">
                                Jadwal Kuliah untuk Kelas: 
                                <span class="fw-bold"><?php echo htmlspecialchars($current_student['nama_kelas']); ?></span>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($jadwal_list)): ?>
                                <div class="alert alert-info text-center m-3">
                                    Belum ada jadwal kuliah yang tersedia untuk kelas Anda.
                                </div>
                            <?php else: ?>
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Hari</th>
                                            <th style="width: 15%;">Waktu</th>
                                            <th>Mata Kuliah</th>
                                            <th>Dosen Pengampu</th>
                                            <th style="width: 5%;">SKS</th>
                                            <th style="width: 10%;">Ruangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($jadwal_list as $jadwal): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                                <td><?php echo date('H:i', strtotime($jadwal['jam_mulai'])) . ' - ' . date('H:i', strtotime($jadwal['jam_selesai'])); ?></td>
                                                <td><?php echo htmlspecialchars($jadwal['nama_mk']); ?></td>
                                                <td><?php echo htmlspecialchars($jadwal['nama_dosen']); ?></td>
                                                <td><?php echo htmlspecialchars($jadwal['sks']); ?></td>
                                                <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <!--end::Card Body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->
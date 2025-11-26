<?php
// ==============================================================
// 1. KONFIGURASI & DATA
// ==============================================================
require_once '../config.php';
require_once 'config/student_identity.php'; // Mengambil data $current_student

// Cek Login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

// Ambil Data Kelas & Semester Mahasiswa
$kelas_id = $current_student['kelas_id'] ?? 0;
$nama_kelas = $current_student['nama_kelas'] ?? 'Umum';
$semester = $current_student['semester'] ?? 1; // Ambil semester dari data login mahasiswa

$jadwal_list = [];

if ($kelas_id > 0) {
    try {
        // Query Jadwal dengan JOIN Lengkap
        $stmt = $pdo->prepare("
            SELECT 
                jk.hari, 
                jk.jam_mulai, 
                jk.jam_selesai, 
                jk.ruangan,
                mk.nama_mk,
                mk.sks,
                mk.kode_mk,
                d.nama_lengkap AS nama_dosen
            FROM jadwal_kuliah jk
            JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
            JOIN dosen d ON jk.dosen_id = d.id
            WHERE jk.kelas_id = :kelas_id
            ORDER BY FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jk.jam_mulai ASC
        ");
        $stmt->execute(['kelas_id' => $kelas_id]);
        $jadwal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Nunito', sans-serif; background-color: #f4f6f9; }

    /* Header Style */
    .page-header-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #2d3748;
    }
    .page-header-subtitle {
        font-size: 1rem;
        color: #718096;
        font-weight: 600;
    }

    /* Card Jadwal Style */
    .jadwal-card {
        border: none;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-left: 5px solid transparent; /* Warna hari */
        position: relative;
    }
    .jadwal-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }

    /* Warna Hari */
    .border-Senin { border-left-color: #4e73df; }
    .border-Selasa { border-left-color: #1cc88a; }
    .border-Rabu { border-left-color: #36b9cc; }
    .border-Kamis { border-left-color: #f6c23e; }
    .border-Jumat { border-left-color: #e74a3b; }
    .border-Sabtu { border-left-color: #858796; }

    .card-content { padding: 20px; flex-grow: 1; }

    /* Badges */
    .day-badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        background: #f8f9fc;
        color: #5a5c69;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        margin-bottom: 10px;
    }
    .sks-badge {
        position: absolute;
        top: 20px; right: 20px;
        background: #eef2ff; color: #4e73df;
        font-weight: 700; font-size: 0.8rem;
        padding: 4px 10px; border-radius: 20px;
    }

    /* Typography */
    .matkul-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
        line-height: 1.4;
    }
    .dosen-name {
        font-size: 0.9rem;
        color: #718096;
        margin-bottom: 15px;
        display: flex; align-items: center; gap: 6px;
    }

    /* Time & Room Box */
    .info-box {
        background: #f7fafc;
        border: 1px dashed #cbd5e0;
        border-radius: 10px;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        color: #4a5568;
    }
    .time-text { font-weight: 700; color: #4e73df; }
    .room-text { font-weight: 600; color: #e74a3b; }

    /* Empty State */
    .empty-state { text-align: center; padding: 50px; color: #a0aec0; }
</style>

<main class="app-main">
    <div class="app-content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="page-header-title mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Kuliah</h3>
                    <p class="page-header-subtitle mb-0">
                        Semester <?= htmlspecialchars($semester) ?> &bull; Kelas <?= htmlspecialchars($nama_kelas) ?>
                    </p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <span class="badge bg-white text-dark shadow-sm px-3 py-2 border">
                        <i class="fas fa-user-graduate me-1"></i> Area Mahasiswa
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row">
                <?php if (empty($jadwal_list)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times fa-4x mb-3"></i>
                                <h5>Belum ada jadwal kuliah.</h5>
                                <p>Hubungi bagian akademik jika jadwal belum muncul.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($jadwal_list as $row): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="jadwal-card border-<?= $row['hari'] ?>">
                                <div class="card-content">
                                    <span class="day-badge"><?= htmlspecialchars($row['hari']) ?></span>
                                    <span class="sks-badge"><?= htmlspecialchars($row['sks']) ?> SKS</span>

                                    <h5 class="matkul-title"><?= htmlspecialchars($row['nama_mk']) ?></h5>
                                    
                                    <div class="dosen-name">
                                        <i class="fas fa-chalkboard-teacher text-muted"></i>
                                        <?= htmlspecialchars($row['nama_dosen']) ?>
                                    </div>

                                    <div class="info-box">
                                        <div class="time-text">
                                            <i class="far fa-clock me-1"></i> 
                                            <?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?>
                                        </div>
                                        <div class="room-text">
                                            <i class="fas fa-map-marker-alt me-1"></i> 
                                            <?= htmlspecialchars($row['ruangan']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

        </div>
    </div>
</main>
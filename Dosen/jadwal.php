<?php
// ==============================================================
// 1. KONFIGURASI & AUTHENTICATION
// ==============================================================
require_once "../config.php"; 

require_login(); 
if ($_SESSION['user_role'] !== 'dosen') {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit;
}

// AMBIL ID DOSEN
$user_id_login = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE user_id = ?");
$stmt->execute([$user_id_login]);
$dosen = $stmt->fetch();

if (!$dosen) die("Error: Data Dosen tidak ditemukan.");
$id_dosen_sekarang = $dosen['id']; 

// ==============================================================
// 2. LOGIC PHP (UPDATE JADWAL)
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- EDIT JADWAL ---
        if (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $id_jadwal   = intval($_POST['id_jadwal']);
            $hari        = $_POST['hari'];
            $jam_mulai   = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $ruangan     = trim($_POST['ruangan']);

            // Validasi: Pastikan jadwal ini milik dosen yang sedang login
            $sql = "UPDATE jadwal_kuliah SET 
                    hari = ?, 
                    jam_mulai = ?, 
                    jam_selesai = ?, 
                    ruangan = ? 
                    WHERE id = ? AND dosen_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hari, $jam_mulai, $jam_selesai, $ruangan, $id_jadwal, $id_dosen_sekarang]);

            if ($stmt->rowCount() > 0) {
                log_activity($pdo, $user_id_login, "Mengubah jadwal ID: $id_jadwal ($hari, $jam_mulai)");
                echo "<script>alert('Jadwal berhasil diperbarui!'); window.location='jadwal.php';</script>";
            } else {
                echo "<script>alert('Tidak ada perubahan data atau Jadwal tidak ditemukan.'); window.location='jadwal.php';</script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// ==============================================================
// 3. QUERY DATA (READ)
// ==============================================================
// Update: Menambahkan logika Semester. 
// Karena di tabel 'kelas' tidak ada kolom semester, kita asumsikan semester diambil dari:
// 1. Tabel mahasiswa (ambil rata-rata/mayoritas semester di kelas itu)
// 2. ATAU hitung manual berdasarkan Angkatan dan Tahun sekarang.
// Di sini saya gunakan hitungan manual Angkatan vs Tahun Sekarang (lebih akurat untuk konteks kelas).

$query = "
    SELECT jk.*, mk.nama_mk, mk.sks, mk.kode_mk, k.kelas, k.angkatan 
    FROM jadwal_kuliah jk
    JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
    JOIN kelas k ON jk.kelas_id = k.id
    WHERE jk.dosen_id = ?
    ORDER BY FIELD(jk.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jk.jam_mulai ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_dosen_sekarang]);
$jadwalList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function hitung semester
function hitungSemester($angkatan) {
    $tahun_sekarang = date('Y');
    $bulan_sekarang = date('n');
    
    $selisih_tahun = $tahun_sekarang - $angkatan;
    
    // Jika bulan sekarang >= Agustus (8), berarti masuk semester Ganjil (1, 3, 5, 7)
    // Jika < Agustus, berarti masih semester Genap tahun ajaran sebelumnya (2, 4, 6, 8)
    if ($bulan_sekarang >= 8) {
        $semester = ($selisih_tahun * 2) + 1;
    } else {
        $semester = ($selisih_tahun * 2);
    }
    
    return ($semester > 0) ? $semester : 1; // Minimal semester 1
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Card Styles */
    .jadwal-card {
        border: none;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        border-left: 5px solid transparent; /* Untuk garis warna hari */
    }
    .jadwal-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    /* Warna Border Kiri Berdasarkan Hari */
    .border-Senin { border-left-color: #4e73df; }
    .border-Selasa { border-left-color: #1cc88a; }
    .border-Rabu { border-left-color: #36b9cc; }
    .border-Kamis { border-left-color: #f6c23e; }
    .border-Jumat { border-left-color: #e74a3b; }
    .border-Sabtu { border-left-color: #858796; }
    .border-Minggu { border-left-color: #5a5c69; }

    .card-body-custom {
        padding: 20px;
        flex-grow: 1;
    }

    /* Typography */
    .day-badge {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 10px;
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        background-color: #f8f9fc;
        color: #5a5c69;
    }
    
    .mk-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2e384d;
        margin-bottom: 5px;
    }
    
    .class-info {
        font-size: 0.95rem; /* Sedikit diperbesar */
        color: #5a5c69;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .time-box {
        background-color: #f0f4ff;
        color: #2c5bf0;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 10px;
        border: 1px dashed #ced4da;
    }

    .room-info {
        font-size: 0.9rem;
        color: #555;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn {
        margin-top: 15px;
        width: 100%;
        border-radius: 8px;
    }
</style>

<div class="app-content-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><i class="fa-regular fa-calendar-days me-2"></i>Jadwal Mengajar</h3>
                <p class="text-muted mb-0 small">Daftar jadwal kuliah yang Anda ampu semester ini.</p>
            </div>
            <div class="col-sm-6 text-end">
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <i class="fas fa-user-tie me-1"></i> Dosen Area
                </span>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchJadwal" class="form-control border-start-0" placeholder="Cari mata kuliah, hari, atau kelas...">
                </div>
            </div>
        </div>

        <div class="row" id="jadwalContainer">
            <?php if(empty($jadwalList)): ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50">
                    <h5 class="text-muted">Tidak ada jadwal mengajar ditemukan.</h5>
                </div>
            <?php else: ?>
                <?php foreach($jadwalList as $row): 
                    // Hitung semester
                    $smt = hitungSemester($row['angkatan']);
                ?>
                    <div class="col-lg-4 col-md-6 mb-4 search-item">
                        <div class="jadwal-card border-<?= $row['hari'] ?>">
                            <div class="card-body-custom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="day-badge"><?= $row['hari'] ?></span>
                                    <span class="badge bg-info text-dark bg-opacity-10 border border-info rounded-pill">
                                        <?= $row['sks'] ?> SKS
                                    </span>
                                </div>

                                <h5 class="mk-title mt-2"><?= htmlspecialchars($row['nama_mk']) ?></h5>
                                
                                <div class="class-info">
                                    <i class="fa-solid fa-users-line text-secondary"></i> 
                                    <span>Semester <?= $smt ?> - Kelas <?= htmlspecialchars($row['kelas']) ?></span>
                                </div>
                                <div class="text-muted small mb-3 ps-4" style="margin-top: -10px;">
                                    Angkatan <?= htmlspecialchars($row['angkatan']) ?>
                                </div>

                                <div class="time-box">
                                    <i class="fa-regular fa-clock me-2"></i>
                                    <?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?>
                                </div>

                                <div class="room-info">
                                    <i class="fa-solid fa-location-dot text-danger"></i>
                                    <strong><?= htmlspecialchars($row['ruangan']) ?></strong>
                                </div>

                                <button class="btn btn-outline-warning text-dark action-btn btn-edit shadow-sm"
                                    data-id="<?= $row['id'] ?>"
                                    data-hari="<?= $row['hari'] ?>"
                                    data-jammulai="<?= $row['jam_mulai'] ?>"
                                    data-jamselesai="<?= $row['jam_selesai'] ?>"
                                    data-ruangan="<?= htmlspecialchars($row['ruangan']) ?>"
                                    data-namamk="<?= htmlspecialchars($row['nama_mk']) ?>"
                                    data-kelas="<?= htmlspecialchars($row['kelas']) ?>"
                                    data-semester="<?= $smt ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalEditJadwal">
                                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Jadwal
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<div class="modal fade" id="modalEditJadwal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-pen-nib me-2"></i>Edit Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_jadwal" id="editId">

                    <div class="alert alert-light border mb-3">
                        <small class="text-muted d-block text-uppercase fw-bold">Mata Kuliah</small>
                        <strong class="text-dark fs-5" id="viewMatkul">-</strong>
                        <div class="mt-1">
                            <small class="text-muted">Target:</small> 
                            <strong id="viewKelasSemester">-</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">HARI</label>
                            <select name="hari" id="editHari" class="form-select" required>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small">JAM MULAI</label>
                            <input type="time" name="jam_mulai" id="editJamMulai" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small">JAM SELESAI</label>
                            <input type="time" name="jam_selesai" id="editJamSelesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">RUANGAN</label>
                        <input type="text" name="ruangan" id="editRuangan" class="form-control" required placeholder="Contoh: Lab Komputer 1">
                    </div>

                    <div class="text-muted small fst-italic">
                        <i class="fas fa-info-circle me-1"></i> Perubahan jadwal akan langsung tampil di halaman mahasiswa.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Search Function
    $("#searchJadwal").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".search-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Populate Modal Edit
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        let hari = $(this).data('hari');
        let mulai = $(this).data('jammulai');
        let selesai = $(this).data('jamselesai');
        let ruang = $(this).data('ruangan');
        let matkul = $(this).data('namamk');
        let kelas = $(this).data('kelas');
        let smt = $(this).data('semester');

        $('#editId').val(id);
        $('#editHari').val(hari);
        $('#editJamMulai').val(mulai);
        $('#editJamSelesai').val(selesai);
        $('#editRuangan').val(ruang);

        $('#viewMatkul').text(matkul);
        $('#viewKelasSemester').text('Semester ' + smt + ' - Kelas ' + kelas);
    });
</script>
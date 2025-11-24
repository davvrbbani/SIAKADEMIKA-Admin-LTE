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

$user_id_login = $_SESSION['user_id'];

// Ambil Data Dosen
$stmt = $pdo->prepare("SELECT id, nama_lengkap FROM dosen WHERE user_id = ?");
$stmt->execute([$user_id_login]);
$dosen_data = $stmt->fetch();

if (!$dosen_data) die("Error: Data Dosen tidak ditemukan.");
$id_dosen_sekarang = $dosen_data['id']; 

// ==============================================================
// 2. LOGIC PHP (CREATE POST & REPLY)
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- BUAT POSTINGAN BARU (FORUM) ---
    if (isset($_POST['action']) && $_POST['action'] === 'tambah_post') {
        try {
            $judul     = trim($_POST['judul']);
            $isi       = trim($_POST['isi']);
            $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
            
            $sql = "INSERT INTO kritik_saran (user_id, tipe, is_anonim, judul, isi, created_at) VALUES (?, 'Publik', ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id_login, $is_anonim, $judul, $isi]);

            echo "<script>alert('Postingan berhasil diterbitkan!'); window.location='./?p=kritiksaran';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    // --- BALAS POSTINGAN (REPLY) ---
    if (isset($_POST['action']) && $_POST['action'] === 'balas_post') {
        try {
            $parent_id = intval($_POST['parent_id']);
            $isi       = trim($_POST['isi']);
            $tipe_asal = $_POST['tipe_asal']; // Publik / Personal
            
            // Dosen membalas (biasanya tidak anonim agar mahasiswa tau itu dosennya, tapi kita kasih opsi default 0)
            $is_anonim = 0; 

            // Insert Balasan (parent_id diisi ID postingan yg dibalas)
            $sql = "INSERT INTO kritik_saran (parent_id, user_id, tipe, is_anonim, isi, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$parent_id, $user_id_login, $tipe_asal, $is_anonim, $isi]);

            echo "<script>alert('Balasan terkirim!'); window.location='./?p=kritiksaran';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error Balas: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// ==============================================================
// 3. QUERY DATA (READ & GROUPING)
// ==============================================================

// Helper function untuk display user
function processUserDisplay($row) {
    $is_anonim = $row['is_anonim'];
    $role = $row['role'];
    
    // Nama Default
    $name = $row['username']; 
    if ($role == 'dosen' && !empty($row['nama_dosen'])) $name = $row['nama_dosen'];
    if ($role == 'mahasiswa' && !empty($row['nama_mhs'])) $name = $row['nama_mhs'];

    $avatar = !empty($row['profile_image']) ? "../" . $row['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($name)."&background=random";

    // Logic Anonim
    if ($is_anonim == 1) {
        $name = "Pengguna Anonim";
        if ($role == 'mahasiswa') $name = "Mahasiswa (Privasi)";
        if ($role == 'dosen') $name = "Dosen (Anonim)";
        $avatar = "https://cdn-icons-png.flaticon.com/512/3135/3135715.png"; 
    }

    return ['name' => $name, 'avatar' => $avatar, 'role' => ucfirst($role)];
}

// A. AMBIL SEMUA BALASAN (REPLIES) DULU
// Kita ambil semua reply lalu dikelompokkan berdasarkan parent_id nya di PHP biar efisien (Eager Loading manual)
$queryReplies = "
    SELECT k.*, u.username, u.role, u.profile_image, d.nama_lengkap as nama_dosen, m.nama_lengkap as nama_mhs
    FROM kritik_saran k
    JOIN users u ON k.user_id = u.id
    LEFT JOIN dosen d ON u.id = d.user_id
    LEFT JOIN mahasiswa m ON u.id = m.user_id
    WHERE k.parent_id IS NOT NULL
    ORDER BY k.created_at ASC
";
$stmtRep = $pdo->query($queryReplies);
$allReplies = $stmtRep->fetchAll(PDO::FETCH_ASSOC);

// Grouping Balasan berdasarkan Parent ID
$repliesGrouped = [];
foreach ($allReplies as $rep) {
    $repliesGrouped[$rep['parent_id']][] = $rep;
}

// B. AMBIL POSTINGAN UTAMA (FORUM PUBLIK)
$queryForum = "
    SELECT k.*, u.username, u.role, u.profile_image, d.nama_lengkap as nama_dosen, m.nama_lengkap as nama_mhs
    FROM kritik_saran k
    JOIN users u ON k.user_id = u.id
    LEFT JOIN dosen d ON u.id = d.user_id
    LEFT JOIN mahasiswa m ON u.id = m.user_id
    WHERE k.tipe = 'Publik' AND k.parent_id IS NULL
    ORDER BY k.created_at DESC
";
$listForum = $pdo->query($queryForum)->fetchAll(PDO::FETCH_ASSOC);

// C. AMBIL POSTINGAN UTAMA (PERSONAL UNTUK DOSEN INI)
$queryPersonal = "
    SELECT k.*, u.username, u.role, u.profile_image, m.nama_lengkap as nama_mhs
    FROM kritik_saran k
    JOIN users u ON k.user_id = u.id
    LEFT JOIN mahasiswa m ON u.id = m.user_id
    WHERE k.tipe = 'Personal' AND k.parent_id IS NULL AND k.target_dosen_id = ?
    ORDER BY k.created_at DESC
";
$stmtPersonal = $pdo->prepare($queryPersonal);
$stmtPersonal->execute([$id_dosen_sekarang]);
$listPersonal = $stmtPersonal->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; background-color: #f4f6f9; }
    
    /* Tabs */
    .nav-pills .nav-link {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 600;
        color: #6c757d;
        background: #fff;
        border: 1px solid #dee2e6;
        margin-right: 10px;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    /* Card Feed */
    .feed-card {
        border: none;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .feed-header {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 12px;
    }
    .user-info h6 { margin: 0; font-weight: 700; color: #333; font-size: 0.95rem; }
    .user-info small { color: #888; font-size: 0.8rem; }
    
    .role-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; margin-left: 5px; text-transform: uppercase; font-weight: bold; }
    .badge-dosen { background-color: #e3f2fd; color: #0d6efd; }
    .badge-mhs { background-color: #fff3cd; color: #ffc107; }
    .badge-anon { background-color: #e9ecef; color: #6c757d; }

    .feed-body { padding: 20px; }
    .post-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: #2c3e50; }
    .post-content { color: #555; line-height: 1.6; }
    
    .feed-footer {
        padding: 10px 20px;
        background: #f8f9fa;
        border-top: 1px solid #eee;
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Balasan Style */
    .reply-section {
        background-color: #fafafa;
        border-top: 1px solid #eee;
        padding: 0 20px;
    }
    .reply-item {
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    .reply-item:last-child { border-bottom: none; }
    .reply-avatar { width: 30px; height: 30px; border-radius: 50%; }
    .reply-content { background: #fff; padding: 10px 15px; border-radius: 0 12px 12px 12px; border: 1px solid #eee; width: 100%; }
    
    .btn-create {
        background: #0d6efd; color: white; border-radius: 50px; padding: 8px 20px; font-weight: 600; border:none;
        box-shadow: 0 4px 10px rgba(13,110,253,0.3);
    }
    .btn-create:hover { background: #0b5ed7; color: white; }
</style>

<div class="app-content-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-0 fw-bold text-dark">Forum & Kritik Saran</h3>
                <p class="text-muted mb-0 small">Diskusi publik dan kotak masuk personal.</p>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-create" data-bs-toggle="modal" data-bs-target="#modalPost">
                    <i class="fas fa-plus me-2"></i> Buat Topik Baru
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="pills-forum-tab" data-bs-toggle="pill" data-bs-target="#pills-forum" type="button">
                    <i class="fas fa-users me-2"></i> Forum Publik
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pills-personal-tab" data-bs-toggle="pill" data-bs-target="#pills-personal" type="button">
                    <i class="fas fa-inbox me-2"></i> Kotak Masuk
                    <?php if(count($listPersonal) > 0): ?>
                        <span class="badge bg-danger ms-1"><?= count($listPersonal) ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-forum">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <?php if(empty($listForum)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                                <h5>Belum ada diskusi.</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach($listForum as $post): 
                                $user = processUserDisplay($post);
                                $postReplies = $repliesGrouped[$post['id']] ?? [];
                                $countRep = count($postReplies);
                            ?>
                            <div class="feed-card">
                                <div class="feed-header">
                                    <img src="<?= htmlspecialchars($user['avatar']) ?>" class="user-avatar">
                                    <div class="user-info">
                                        <h6>
                                            <?= htmlspecialchars($user['name']) ?>
                                            <span class="role-badge <?= $post['is_anonim'] ? 'badge-anon' : ($user['role']=='Dosen'?'badge-dosen':'badge-mhs') ?>">
                                                <?= $post['is_anonim'] ? 'Anonim' : $user['role'] ?>
                                            </span>
                                        </h6>
                                        <small><?= date('d M Y, H:i', strtotime($post['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="feed-body">
                                    <div class="post-title"><?= htmlspecialchars($post['judul']) ?></div>
                                    <div class="post-content"><?= nl2br(htmlspecialchars($post['isi'])) ?></div>
                                </div>
                                
                                <div class="feed-footer">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-reply"
                                        data-id="<?= $post['id'] ?>"
                                        data-tipe="Publik"
                                        data-judul="<?= htmlspecialchars($post['judul']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalReply">
                                        <i class="fas fa-reply me-1"></i> Balas
                                    </button>
                                    
                                    <?php if($countRep > 0): ?>
                                    <button class="btn btn-sm btn-link text-secondary text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReply<?= $post['id'] ?>">
                                        <?= $countRep ?> Komentar <i class="fas fa-chevron-down ms-1"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <?php if($countRep > 0): ?>
                                <div class="collapse reply-section" id="collapseReply<?= $post['id'] ?>">
                                    <?php foreach($postReplies as $rep): 
                                        $repUser = processUserDisplay($rep);
                                    ?>
                                    <div class="reply-item">
                                        <img src="<?= htmlspecialchars($repUser['avatar']) ?>" class="reply-avatar">
                                        <div class="reply-content">
                                            <div class="d-flex justify-content-between">
                                                <strong class="small text-dark">
                                                    <?= htmlspecialchars($repUser['name']) ?>
                                                    <span class="text-muted fw-normal" style="font-size:0.7rem">• <?= $repUser['role'] ?></span>
                                                </strong>
                                                <small class="text-muted" style="font-size:0.7rem"><?= date('d/m H:i', strtotime($rep['created_at'])) ?></small>
                                            </div>
                                            <div class="small text-secondary mt-1"><?= nl2br(htmlspecialchars($rep['isi'])) ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <div class="pb-3"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-personal">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <?php if(empty($listPersonal)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <h5>Kotak masuk kosong.</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach($listPersonal as $msg): 
                                $user = processUserDisplay($msg);
                                $postReplies = $repliesGrouped[$msg['id']] ?? [];
                                $countRep = count($postReplies);
                            ?>
                            <div class="feed-card border-start border-4 border-warning">
                                <div class="feed-header">
                                    <img src="<?= htmlspecialchars($user['avatar']) ?>" class="user-avatar">
                                    <div class="user-info">
                                        <h6>
                                            <?= htmlspecialchars($user['name']) ?>
                                            <span class="role-badge badge-mhs">Mahasiswa</span>
                                        </h6>
                                        <small><?= date('d M Y, H:i', strtotime($msg['created_at'])) ?></small>
                                    </div>
                                    <div class="ms-auto"><span class="badge bg-warning text-dark">Personal</span></div>
                                </div>
                                <div class="feed-body">
                                    <div class="post-title"><?= htmlspecialchars($msg['judul']) ?></div>
                                    <div class="post-content"><?= nl2br(htmlspecialchars($msg['isi'])) ?></div>
                                </div>
                                <div class="feed-footer">
                                    <!-- <button class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 btn-reply"
                                        data-id="<?= $msg['id'] ?>"
                                        data-tipe="Personal"
                                        data-judul="<?= htmlspecialchars($msg['judul']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalReply">
                                        <i class="fas fa-reply me-1"></i> Balas Personal
                                    </button> -->
                                    
                                    <?php if($countRep > 0): ?>
                                    <button class="btn btn-sm btn-link text-secondary text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReply<?= $msg['id'] ?>">
                                        Lihat <?= $countRep ?> Balasan <i class="fas fa-chevron-down ms-1"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <?php if($countRep > 0): ?>
                                <div class="collapse reply-section" id="collapseReply<?= $msg['id'] ?>">
                                    <?php foreach($postReplies as $rep): 
                                        $repUser = processUserDisplay($rep);
                                    ?>
                                    <div class="reply-item">
                                        <img src="<?= htmlspecialchars($repUser['avatar']) ?>" class="reply-avatar">
                                        <div class="reply-content">
                                            <div class="d-flex justify-content-between">
                                                <strong class="small text-dark"><?= htmlspecialchars($repUser['name']) ?></strong>
                                                <small class="text-muted" style="font-size:0.7rem"><?= date('d/m H:i', strtotime($rep['created_at'])) ?></small>
                                            </div>
                                            <div class="small text-secondary mt-1"><?= nl2br(htmlspecialchars($rep['isi'])) ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <div class="pb-3"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalPost" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Buat Topik Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="tambah_post">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">JUDUL</label>
                        <input type="text" name="judul" class="form-control" required placeholder="Contoh: Info Ujian Tengah Semester">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ISI PESAN</label>
                        <textarea name="isi" class="form-control" rows="4" required placeholder="Tuliskan sesuatu..."></textarea>
                    </div>
                    <div class="form-check form-switch p-2 bg-light rounded border">
                        <input class="form-check-input ms-1" type="checkbox" name="is_anonim">
                        <label class="form-check-label fw-bold ms-3">Posting sebagai Anonim</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReply" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-reply me-2"></i>Tulis Balasan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="balas_post">
                    <input type="hidden" name="parent_id" id="replyParentId">
                    <input type="hidden" name="tipe_asal" id="replyTipeAsal">
                    
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted">Membalas topik:</small>
                        <div class="fw-bold text-dark" id="replyTitleDisplay">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ISI BALASAN</label>
                        <textarea name="isi" class="form-control" rows="4" required placeholder="Tulis balasan Anda..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-success px-4 fw-bold">Kirim Balasan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Pass data to Reply Modal
    $(document).on('click', '.btn-reply', function() {
        let id = $(this).data('id');
        let judul = $(this).data('judul');
        let tipe = $(this).data('tipe');

        $('#replyParentId').val(id);
        $('#replyTipeAsal').val(tipe);
        $('#replyTitleDisplay').text(judul);
    });
</script>
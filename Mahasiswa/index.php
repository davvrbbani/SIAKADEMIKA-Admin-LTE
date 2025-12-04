<?php
require_once '../config.php';
require_once 'config/student_identity.php'; // Wajib untuk identitas mahasiswa

// Cek Login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit;
}

// --- AMBIL FOTO PROFIL DARI TABEL USERS ---
// Kita ambil dari tabel users karena foto profil disimpan di sana
$stmtImg = $pdo->prepare("SELECT profile_image, email FROM users WHERE id = ?");
$stmtImg->execute([$_SESSION['user_id']]);
$userData = $stmtImg->fetch(PDO::FETCH_ASSOC);

// Logic Foto Profil
$db_img = $userData['profile_image'];
$profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($current_student['nama_lengkap']) . "&background=random"; // Default Avatar

if (!empty($db_img) && file_exists("../" . $db_img)) {
    $profile_pic = "../" . $db_img;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SIAKADEMIKA | Mahasiswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="../assets/css/adminlte.css" />

    <style>
        .brand-image {
            opacity: .8;
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }
        /* Agar foto profil di navbar tidak gepeng */
        .user-image { object-fit: cover; }
    </style>
  </head>
  
  <body class="sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
      
      <nav class="app-header navbar navbar-expand navbar-white navbar-light">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
      <div>
          <span class="nav-item align-items-centre ">Sistem Informasi Akademik Informatika</span>
      </div>
          <ul class="navbar-nav ms-auto">
            
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>

            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="<?php echo $profile_pic; ?>" class="user-image rounded-circle shadow" alt="User Image" />
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_student['nama_lengkap']); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?php echo $profile_pic; ?>" class="rounded-circle shadow" alt="User Image" />
                  <p>
                    <?php echo htmlspecialchars($current_student['nama_lengkap']); ?>
                    <small>NIM: <?php echo htmlspecialchars($current_student['NIM']); ?></small>
                  </p>
                </li>
                <li class="user-footer">
                  <a href="./?p=profile" class="btn btn-default btn-flat">Profile</a>
                  <a href="../logout.php" class="btn btn-default btn-flat float-end">Logout</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      
      <aside class="app-sidebar bg-primary-subtle" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="./" class="brand-link">
            <img src="../assets/img/informatika.jpg" alt="Logo" class="brand-image shadow">
            <span class="brand-text fw-light">SIAKADEMIKA</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
              
              <li class="nav-header">MENU UTAMA</li>
              
              <li class="nav-item">
                <a href="./" class="nav-link">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=jadwal-kuliah" class="nav-link">
                  <i class="nav-icon bi bi-calendar-week"></i>
                  <p>Jadwal Kuliah</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=materi-kuliah" class="nav-link">
                  <i class="nav-icon bi bi-journal-richtext"></i>
                  <p>Materi Kuliah</p>
                </a>
              </li>

              <li class="nav-header">AKTIVITAS</li>

              <li class="nav-item">
                <a href="./?p=prestasi-mahasiswa" class="nav-link">
                  <i class="nav-icon bi bi-trophy"></i>
                  <p>Prestasi Kelas</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=kritik-saran" class="nav-link">
                  <i class="nav-icon bi bi-chat-dots"></i>
                  <p>Kritik & Saran</p>
                </a>
              </li>

              <li class="nav-header">PENGATURAN</li>

              <li class="nav-item">
                <a href="./?p=profile" class="nav-link">
                  <i class="nav-icon bi bi-person-circle"></i>
                  <p>Profil Saya</p>
                </a>
              </li>

            </ul>
          </nav>
        </div>
      </aside>

      <?php require_once "route.php"; ?>

    </div> 
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/adminlte.js"></script>
  </body>
</html>
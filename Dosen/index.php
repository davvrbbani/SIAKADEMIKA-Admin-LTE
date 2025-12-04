<?php
require_once '../config.php';

// Cek Login
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'dosen') {
    header("Location: ../index.php");
    exit;
}

// --- AMBIL DATA DOSEN & FOTO PROFIL ---
$user_id = $_SESSION['user_id'];

// Ambil foto dari tabel users dan nama dari tabel dosen
$stmtInfo = $pdo->prepare("
    SELECT u.username, u.email, u.profile_image, d.nama_lengkap 
    FROM users u 
    JOIN dosen d ON u.id = d.user_id 
    WHERE u.id = ?
");
$stmtInfo->execute([$user_id]);
$dosenInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

// Logic Foto Profil
$db_img = $dosenInfo['profile_image'];
$profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($dosenInfo['nama_lengkap']) . "&background=random"; // Default

if (!empty($db_img) && file_exists("../" . $db_img)) {
    $profile_pic = "../" . $db_img;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SIAKADEMIKA | Dosen</title>
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
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($dosenInfo['nama_lengkap']); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img src="<?php echo $profile_pic; ?>" class="rounded-circle shadow" alt="User Image" />
                  <p>
                    <?php echo htmlspecialchars($dosenInfo['nama_lengkap']); ?>
                    <small>Dosen Pengajar</small>
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
              
              <li class="nav-header">MENU DOSEN</li>
              
              <li class="nav-item">
                <a href="./" class="nav-link">
                  <i class="nav-icon bi bi-house-door-fill"></i>
                  <p>Home</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=profile" class="nav-link">
                  <i class="nav-icon bi bi-person-circle"></i>
                  <p>Profile</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=jadwal" class="nav-link">
                  <i class="nav-icon bi bi-calendar-week"></i>
                  <p>Jadwal Mengajar</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=materi" class="nav-link">
                  <i class="nav-icon bi bi-book-fill"></i>
                  <p>Materi Kuliah</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./?p=kritiksaran" class="nav-link">
                  <i class="nav-icon bi bi-chat-quote-fill"></i>
                  <p>Kritik & Saran</p>
                </a>
              </li>

            </ul>
          </nav>
        </div>
      </aside>

      <main class="app-main">
        <?php include __DIR__ . '/route.php'; ?>
      </main>

    </div> 
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/adminlte.js"></script>
  </body>
</html>
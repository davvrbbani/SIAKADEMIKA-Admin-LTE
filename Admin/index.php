<?php
require_once '../config.php';
require_login(); // Ini sudah menjalankan session_start()

// Pengecekan role, sudah benar
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- MODIFIKASI DIMULAI ---
// Ambil variabel dari session untuk ditampilkan.
// Gunakan htmlspecialchars untuk keamanan (mencegah XSS)
$username = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
// Ambil role dan ubah huruf pertamanya menjadi kapital
$userrole = ucfirst(htmlspecialchars($_SESSION['user_role'], ENT_QUOTES, 'UTF-8'));
// --- MODIFIKASI SELESAI ---
?>
<!doctype html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>SIAKADEMIKA | Sistem Akademik Informatika A</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="AdminLTE 4 | Theme Customize" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../assets/css/adminlte.css" as="style" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="../assets/css/adminlte.css" />
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
            <li class="nav-item d-none d-md-block"><a href="?p=dashboard" class="nav-link">Home</a></li>
            <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="bi bi-search"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-chat-text"></i>
                <span class="navbar-badge badge text-bg-danger">3</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <a href="#" class="dropdown-item">
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="../assets/img/user1-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        Brad Diesel
                        <span class="float-end fs-7 text-danger"
                          ><i class="bi bi-star-fill"></i
                        ></span>
                      </h3>
                      <p class="fs-7">Call me whenever you can...</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="../assets/img/user8-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        John Pierce
                        <span class="float-end fs-7 text-secondary">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </h3>
                      <p class="fs-7">I got your message bro</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="../assets/img/user3-128x128.jpg"
                        alt="User Avatar"
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <h3 class="dropdown-item-title">
                        Nora Silvester
                        <span class="float-end fs-7 text-warning">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </h3>
                      <p class="fs-7">The subject goes here</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell-fill"></i>
                <span class="navbar-badge badge text-bg-warning">15</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-envelope me-2"></i> 4 new messages
                  <span class="float-end text-secondary fs-7">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-people-fill me-2"></i> 8 friend requests
                  <span class="float-end text-secondary fs-7">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                  <span class="float-end text-secondary fs-7">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="../assets/img/Aviators.jpg"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline"><?php echo $username; ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <img
                    src="../assets/img/Aviators.jpg"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    <?php echo $username; ?> - <?php echo $userrole; ?>
                    <small>Member since Nov. 2023</small>
                  </p>
                </li>
                <li class="user-body">
                  <div class="row">
                    <div class="col-4 text-center"><a href="#">Followers</a></div>
                    <div class="col-4 text-center"><a href="#">Sales</a></div>
                    <div class="col-4 text-center"><a href="#">Friends</a></div>
                  </div>
                  </li>
                <li class="user-footer">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                  <a href="../logout.php" class="btn btn-default btn-flat float-end">Logout</a>
                </li>
                </ul>
            </li>
            </ul>
          </div>
        </nav>
      <aside class="app-sidebar bg-primary-subtle" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="?p=dashboard" class="brand-link">
            <i class="brand-icon bi bi-mortarboard-fill"></i>
            <span class="brand-text">SIAKADEMIKA</span>
        </a>
          </div>
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false"
            >
              <li class="nav-header">MENU UTAMA</li>
              <li class="nav-item">
                <a href="?p=dashboard" class="nav-link">
                  <i class="nav-icon bi bi-house-door-fill"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-item">
                  <a href="./?p=dosen" class="nav-link">
                      <i class="nav-icon bi bi-person-circle"></i>
                      <p>Profile</p>
                  </a>
              </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-clipboard-data-fill"></i>
                    <p>
                        Data-Data
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="./?p=dosen" class="nav-link">
                            <i class="nav-icon bi bi-mortarboard-fill"></i>
                            <p>Data Dosen</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./?p=mahasiswa" class="nav-link">
                            <i class="nav-icon bi bi-person-fill"></i>
                            <p>Data Mahasiswa</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./?p=matakuliah" class="nav-link">
                            <i class="nav-icon bi bi-book-fill"></i>
                            <p>Data Matakuliah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./?p=kelas" class="nav-link">
                            <i class="nav-icon bi bi-buildings-fill"></i>
                            <p>Data Kelas</p>
                        </a>
                    </li>
                </ul>
            </li> 
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-building-fill"></i>
                    <p>
                        Akademik
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="./?p=jadwal-kuliah" class="nav-link">
                            <i class="nav-icon bi bi-calendar3"></i>
                            <p>Jadwal Kuliah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./?p=materi-kuliah" class="nav-link">
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>Materi Kuliah</p>
                        </a>
                    </li>
                    </ul>
                <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-archive-fill"></i>
                    <p>
                        Pelaporan Akademik
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="./?p=manage-prestasi" class="nav-link">
                            <i class="nav-icon bi bi-trophy-fill"></i>
                            <p>Laporan Prestasi Mahasiswa</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./?p=manage-kritik" class="nav-link">
                            <i class="nav-icon bi bi-pencil-square"></i>
                            <p>Laporan Kritik & Saran</p>
                        </a>
                    </li>
                </ul>
         </li>
            </li>
            </ul>
            </nav>
        </div>
        </aside>
      <?php
      require_once "route.php";
      ?>
      <footer class="app-footer bg-black" data-bs-theme="dark">
        <strong>
          Copyright &copy;2025&nbsp;
          <a href="#" class="text-decoration-none">SIAKADEMIKA</a>.
        </strong>
        </footer>
      </div>
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="../assets/js/adminlte.js"></script>
    </body>
  </html>
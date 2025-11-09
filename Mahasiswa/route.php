<?php
// Get the page parameter 'p' from the URL
$p = isset($_GET['p']) ? $_GET['p'] : '';

// Route to the correct page based on the parameter
switch ($p) {
    case 'jadwal-kuliah':
        include "jadwal-kuliah.php";
        break;
    
    case 'materi-kuliah':
        include "materi-kuliah.php";
        break;

    case 'prestasi-mahasiswa':
        include "prestasi-mahasiswa.php";
        break;

    // NEWLY ADDED CASE
    case 'kritik-saran':
        include "kritik-saran.php";
        break;
    case 'lihat-postingan':
        include "lihatpostingan.php";
        break;
    case 'forum-balasan':
        include "postingan&balasankritiksaran.php";
        break;
    case 'profile':
        include "profile.php";
        break;



// ... (the other cases for 'jadwal-kuliah', etc. remain the same) ...

    // The default page to show when no 'p' is provided or it doesn't match
    default:
?>
    <!-- Custom CSS to make the entire small-box clickable and fix symmetry -->
    <style>
        .clickable-box-link {
            text-decoration: none; /* Removes the underline from the link */
        }
        .small-box {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .clickable-box-link:hover .small-box {
            transform: translateY(-5px); /* Adds a subtle lift on hover */
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .small-box .small-box-footer {
            /* This is the key to fixing the symmetry */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 0; /* Adjust padding for a better look */
        }
        .small-box .icon {
            display: flex;
            align-items: center;       /* vertical center */
            justify-content: center;   /* horizontal center */
            height: 100%;              /* makes the centering consistent */
            font-size: 3rem;           /* optional: resize icon */
        }

    </style>
    <!--begin::App Main-->
    <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Dashboard</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="./">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
                        <!-- Welcome Message -->
                        <div class="alert alert-primary">
                            <h5>Selamat Datang, <?php echo htmlspecialchars($current_student['nama_lengkap']); ?>!</h5>
                            <p>Selamat datang di Dashboard Sistem Informasi Akademik. Silakan pilih menu di bawah ini untuk memulai.</p>
                        </div>
                    </div>
                </div>
                <!-- Info Boxes -->
                <div class="row">
                    <!-- Jadwal Matkul -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <!-- The entire box is now wrapped in an <a> tag -->
                        <a href="./?p=jadwal-kuliah" class="clickable-box-link">
                            <div class="small-box text-bg-primary">
                                <div class="inner">
                                    <h3>Jadwal Matkul</h3>
                                    <p>Lihat jadwal kuliah mingguan</p>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-calendar-week"></i>
                                </div>
                                <!-- The original <a> footer is now a <div> for styling -->
                                <div class="small-box-footer">
                                    Lihat Detail <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Materi Kuliah -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <a href="./?p=materi-kuliah" class="clickable-box-link">
                            <div class="small-box text-bg-success">
                                <div class="inner">
                                    <h3>Materi Kuliah</h3>
                                    <p>Download materi perkuliahan</p>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-book"></i>
                                </div>
                                <div class="small-box-footer">
                                    Lihat Detail <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Kejuaraan -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <a href="./?p=prestasi-mahasiswa" class="clickable-box-link">
                            <div class="small-box text-bg-warning">
                                <div class="inner">
                                    <h3>Kejuaraan</h3>
                                    <p>Kelola data prestasi Anda</p>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div class="small-box-footer">
                                    Lihat Detail <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Kritik dan Saran -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <a href="./?p=kritik-saran" class="clickable-box-link">
                            <div class="small-box text-bg-danger">
                                <div class="inner">
                                    <h3>Kritik & Saran</h3>
                                    <p>Kirim masukan untuk kampus</p>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-chat-right-quote"></i>
                                </div>
                                <div class="small-box-footer">
                                    Lihat Detail <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--end::App Content-->
    </main>
    <!--end::App Main-->
<?php
        break; // End of the default case
}
?>

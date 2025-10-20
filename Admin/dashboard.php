<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <!-- Kolom Kiri: Judul dan Ucapan Selamat Datang -->
                <div class="col-sm-6">
                    <p class="mb-1 fs-5 text-muted">Hai, Selamat Datang User!</p>
                    <h3 class="mb-0">Dashboard Admin</h3>
                </div>
                <!-- Kolom Kanan: Breadcrumb -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="?p=dashboard">Home</a></li>
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
            <!-- Baris untuk Kartu Statistik -->
            <div class="row">

                <!-- Kartu Statistik: Total Dosen -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1"></h1>
                                <p class="card-text mb-0">Total Dosen</p>
                            </div>
                            <i class="bi bi-mortarboard-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=dosen" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

                <!-- Kartu Statistik: Total Mahasiswa -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1"></h1>
                                <p class="card-text mb-0">Total Mahasiswa</p>
                            </div>
                            <i class="bi bi-people-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=mahasiswa" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

                <!-- Anda bisa menambahkan kartu statistik lain di sini -->
                <!-- Contoh: Kartu Statistik Mata Kuliah -->
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="card-title text-white mb-1">...</h1>
                                <p class="card-text mb-0">Mata Kuliah</p>
                            </div>
                            <i class="bi bi-book-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <a href="?p=matakuliah" class="card-footer text-white text-decoration-none">
                            Lihat Detail <i class="bi bi-arrow-right-circle-fill"></i>
                        </a>
                    </div>
                </div>

            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

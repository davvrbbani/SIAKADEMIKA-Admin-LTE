<?php
// Cek apakah parameter 'p' ada di URL, 
// jika ada, gunakan nilainya. Jika tidak, gunakan string kosong.
$p = isset($_GET['p']) ? $_GET['p'] : '';

// Sekarang variabel $p berisi string "dosen", "mahasiswa", dll.
switch ($p) {
    case 'dosen':
        include "dosen.php";
        break;
    case 'mahasiswa':
        include "mahasiswa.php";
        break;
    case 'add-mahasiswa':
        include "add-mahasiswa.php";
        break;
    case 'Pegawai':
        include "Pegawai.php";
        break;
    // Sebaiknya ada halaman default untuk ditampilkan
    default:
        echo "<main class='app-main'><div class='app-content p-4'><h3>Selamat Darang di Dashboard SIAKAD</h3></div></main>";
        break;
}
?>
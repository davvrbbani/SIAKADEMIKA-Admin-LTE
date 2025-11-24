<?php
// Cek apakah parameter 'p' ada di URL, 
// jika ada, gunakan nilainya. Jika tidak, gunakan string kosong.
$p = isset($_GET['p']) ? $_GET['p'] : '';

// Sekarang variabel $p berisi string "dosen", "mahasiswa", dll.
switch ($p) {
    case 'jadwal':
        include "jadwal.php";
        break;
    case 'materi':
        include "materi.php";
        break;
    case 'profile':
        include "profile.php";
        break;
    case 'kritiksaran':
        include "kritiksaran.php";
        break;
    // Sebaiknya ada halaman default untuk ditampilkan
    default:
        include "dashboard.php";
        break;
}
?>
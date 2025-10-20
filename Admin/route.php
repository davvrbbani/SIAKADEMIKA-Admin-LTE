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
    // Sebaiknya ada halaman default untuk ditampilkan
    default:
        include "dashboard.php";
    break;
}
?>
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

    // ... (the other cases remain the same) ...

    default:
    require_once "dashboard.php";
        // ... (default dashboard code) ...
    };
?>
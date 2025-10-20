<?php
// 1. Mulai (atau lanjutkan) sesi yang ada
// Ini wajib dipanggil sebelum bisa mengakses atau menghancurkan sesi.
session_start();

// 2. Hapus semua variabel sesi
// Ini membersihkan data seperti $_SESSION['user_name'], $_SESSION['user_role'], dll.
session_unset();

// 3. Hancurkan sesi
// Ini menghapus file sesi di server dan mengakhiri sesi secara formal.
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login
// (Asumsi halaman login Anda adalah 'login.php' di root)
header("Location: index.php");
exit; // Pastikan tidak ada kode lain yang dieksekusi setelah redirect
?>
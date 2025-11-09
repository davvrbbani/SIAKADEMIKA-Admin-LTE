<?php
// Cek apakah parameter 'p' ada di URL, 
// jika ada, gunakan nilainya. Jika tidak, gunakan string kosong.
$p = isset($_GET['p']) ? $_GET['p'] : '';

// Sekarang variabel $p berisi string "dosen", "mahasiswa", dll.
switch ($p) {
    case 'dosen':
        include "datadosen/dosen.php";
        break;
    case 'add-dosen':
        include "datadosen/add-dosen.php";
        break;
    case 'editdsn':
        include "datadosen/editdosen.php";
        break;
    case 'detaildsn':
        include "datadosen/detaildosen.php";
        break;
    case 'hapusdsn':
        include "datadosen/hapusdosen.php";
        break;
    case 'mahasiswa':
        include "datamhs/mahasiswa.php";
        break;
    case 'matakuliah':
        include "datamatkul/matkul.php";
        break;
    case 'addmatakuliah':
        include "datamatkul/addmatkul.php";
        break;
    case 'detailmatakuliah':
        include "datamatkul/detailmatkul.php";
        break;
    case 'editmatakuliah':
        include "datamatkul/editmatkul.php";
        break;
    case 'hapusmatakuliah':
        include "datamatkul/hapusmatkul.php";
        break;
    case 'add-mahasiswa':
        include "datamhs/add-mahasiswa.php";
        break;
    case 'detail-mahasiswa':
        include "datamhs/detailmhs.php";
        break;
    case 'edit-mahasiswa':
        include "datamhs/editmhs.php";
        break;
    case 'hapus-mahasiswa':
        include "hapusmhs.php";
        break;
    case 'kelas':
        include "datakelas/kelas.php";
        break;
    case 'detail-kelas':
        include "datakelas/detailkelas.php";
        break;
    case 'edit-kelas':
        include "datakelas/editkelas.php";
        break;
    case 'add-kelas':
        include "datakelas/addkelas.php";
        break;
    case 'hapus-kelas':
        include "datakelas/hapuskelas.php";
        break;
    case 'jadwal-kuliah':
        include "jadwalkuliah/jadwalkuliah.php";
        break;
    case 'add-jadwal':
        include "jadwalkuliah/addjadwal.php";
        break;
    case 'detail-jadwal':
        include "jadwalkuliah/detailjadwal.php";
        break;
    case 'edit-jadwal':
        include "jadwalkuliah/editjadwal.php";
        break;
    case 'hapus-jadwal':
        include "jadwalkuliah/hapusjadwal.php";
        break;
    case 'materi-kuliah':
        include "datamaterikuliah/manage-materi.php";
        break;
    case 'add-materi':
        include "datamaterikuliah/add-materi-admin.php";
        break;
    case 'edit-materi':
        include "datamaterikuliah/edit-materi.php";
        break;
    case 'detail-materi':
        include "datamaterikuliah/detail-materi.php";
        break;
    case 'hapus-materi':
        include "datamaterikuliah/hapus-materi.php";
        break;
    case 'detail-materi':
        include "datamaterikuliah/detail-materi.php";
        break;
    case 'manage-prestasi':
        include "dataprestasi/prestasimahasiswa.php";
        break;
    case 'detail-prestasi':
        include "dataprestasi/detailprestasi.php";
        break;
    case 'edit-prestasi':
        include "dataprestasi/editprestasi.php";
        break;
    case 'add-prestasi':
        include "dataprestasi/addprestasi.php";
        break;
    case 'hapus-prestasi':
        include "dataprestasi/hapusprestasi.php";
        break;
    case 'manage-kritik':
        include "datakritik&saran/forumkritik&saran.php";
        break;
    case 'detail-kritik-admin':
        include "datakritik&saran/detailkritik.php";
        break;
    case 'edit-kritik-admin':
        include "datakritik&saran/editkritik.php";
        break;
    case 'add-kritik-admin':
        include "datakritik&saran/addkritik.php";
        break;
    case 'hapus-kritik':
        include "datakritik&saran/hapuskritik.php";
        break;
        // Sebaiknya ada halaman default untuk ditampilkan
    default:
        include "dashboard.php";
    break;
}
?>
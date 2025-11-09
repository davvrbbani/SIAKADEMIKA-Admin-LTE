<?php
// This file should be included at the top of every student-facing page.

// 1. Ensure the user is logged in.
// We assume a global config file is already included that starts the session.
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the main login page.
    // The path '../index.php' assumes this file is in a subdirectory like Mahasiswa/
    header("Location: ../index.php");
    exit;
}

// 2. Check if the user is actually a student.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'mahasiswa') {
    // If the user is an admin or dosen, they should not be here.
    // Send them to logout or an error page.
    // For simplicity, we'll send them to logout.
    header("Location: ../logout.php");
    exit;
}

// 3. Fetch the logged-in student's specific profile from the 'mahasiswa' table.
// We use the PDO object $pdo from your main config file.
try {
    $stmt = $pdo->prepare(
        "SELECT id, NIM, nama_lengkap, semester, kelas_id 
         FROM mahasiswa 
         WHERE user_id = :user_id 
         LIMIT 1"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $current_student = $stmt->fetch();

    if (!$current_student) {
        // This is a critical error. The user is logged in, but has no corresponding student profile.
        // This could happen if an admin deleted the student profile but not the user account.
        // We should handle this gracefully.
        die("Error: Profil mahasiswa tidak ditemukan. Silakan hubungi administrator.");
    }

    // Additionally, let's fetch the class name for convenience
    if ($current_student['kelas_id']) {
        $kelas_stmt = $pdo->prepare("SELECT kelas FROM kelas WHERE id = :kelas_id");
        $kelas_stmt->execute(['kelas_id' => $current_student['kelas_id']]);
        $kelas_info = $kelas_stmt->fetch();
        $current_student['nama_kelas'] = $kelas_info ? $kelas_info['kelas'] : 'N/A';
    } else {
        $current_student['nama_kelas'] = 'Belum Terdaftar';
    }


} catch (PDOException $e) {
    // In a real application, you might log this error instead of showing it to the user.
    die("Error: Tidak dapat mengambil data mahasiswa: " . $e->getMessage());
}

// From now on, any page that includes this file will have the $current_student variable
// available, containing all the necessary profile information.
// e.g., echo $current_student['nama_lengkap'];
?>
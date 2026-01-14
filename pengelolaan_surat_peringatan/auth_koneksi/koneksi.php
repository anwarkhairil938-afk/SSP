<?php
$host = "localhost";
$user = "root"; // atau 'surat_user' jika pakai user khusus
$pass = "";
$db   = "pengelolaan_surat_peringatan";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($koneksi, "utf8mb4");

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>
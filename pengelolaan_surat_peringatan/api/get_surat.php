<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil ID surat dari GET
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID surat tidak ditemukan']);
    exit();
}

$surat_id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Query untuk mendapatkan data surat
$query = "SELECT s.*, m.nama as nama_mahasiswa, m.nim, m.program_studi, m.semester 
          FROM surat_peringatan s 
          JOIN mahasiswa m ON s.mahasiswa_id = m.id 
          WHERE s.id = '$surat_id'";

$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data surat tidak ditemukan']);
}

mysqli_close($koneksi);
?>
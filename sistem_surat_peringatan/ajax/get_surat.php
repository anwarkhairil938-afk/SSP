<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT s.*, m.nama as mahasiswa_nama, m.nim, m.program_studi, m.semester 
          FROM surat_peringatan s 
          JOIN mahasiswa m ON s.mahasiswa_id = m.id 
          WHERE s.id = '$id'";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}
?>
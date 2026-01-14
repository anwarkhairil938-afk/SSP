<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil ID mahasiswa dari GET
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID mahasiswa tidak ditemukan']);
    exit();
}

$mahasiswa_id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Query untuk mendapatkan data mahasiswa
$query = "SELECT m.*, u.username 
          FROM mahasiswa m 
          JOIN users u ON m.user_id = u.id 
          WHERE m.id = '$mahasiswa_id'";

$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan']);
}

mysqli_close($koneksi);
?>
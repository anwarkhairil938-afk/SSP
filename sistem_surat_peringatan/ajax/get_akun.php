<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT u.*, m.nim 
          FROM users u 
          LEFT JOIN mahasiswa m ON u.id = m.user_id 
          WHERE u.id = '$id'";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // Jangan tampilkan password hash
    unset($row['password']);
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}
?>
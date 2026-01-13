<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Jika id kosong, ambil dari dropdown selection
if (!$id && isset($_GET['mahasiswa_id'])) {
    $id = $_GET['mahasiswa_id'];
}

$query = "SELECT * FROM mahasiswa WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';
$program_studi = isset($_GET['program_studi']) ? mysqli_real_escape_string($koneksi, $_GET['program_studi']) : '';

// Gunakan procedure atau query langsung
$query = "CALL get_mahasiswa_filtered('$status', '$program_studi', '$search')";
$result = mysqli_query($koneksi, $query);

$mahasiswa_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $mahasiswa_list[] = $row;
}

// Clear results jika menggunakan procedure
while (mysqli_next_result($koneksi)) {
    if ($result = mysqli_store_result($koneksi)) {
        mysqli_free_result($result);
    }
}

echo json_encode(['success' => true, 'data' => $mahasiswa_list]);
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil parameter filter
$status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';
$jenis = isset($_GET['jenis']) ? mysqli_real_escape_string($koneksi, $_GET['jenis']) : '';
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($koneksi, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($koneksi, $_GET['end_date']) : '';

// Gunakan view atau query langsung
$query = "SELECT * FROM view_surat_lengkap WHERE 1=1";

if (!empty($status)) {
    $query .= " AND status = '$status'";
}

if (!empty($jenis)) {
    $query .= " AND jenis_pelanggaran = '$jenis'";
}

if (!empty($start_date)) {
    $query .= " AND tanggal_surat >= '$start_date'";
}

if (!empty($end_date)) {
    $query .= " AND tanggal_surat <= '$end_date'";
}

$query .= " ORDER BY created_at DESC";

$result = mysqli_query($koneksi, $query);

$surat_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $surat_list[] = $row;
}

echo json_encode(['success' => true, 'data' => $surat_list]);
?>
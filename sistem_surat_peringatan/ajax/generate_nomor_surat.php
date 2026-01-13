<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Cari nomor surat terakhir tahun ini
$query = "SELECT nomor_surat FROM surat_peringatan WHERE nomor_surat LIKE 'SP/$year/%' ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $last_number = explode('/', $row['nomor_surat']);
    $next_number = intval(end($last_number)) + 1;
} else {
    $next_number = 1;
}

// Format nomor dengan leading zeros
$formatted_number = str_pad($next_number, 3, '0', STR_PAD_LEFT);

echo json_encode(['success' => true, 'nomor' => $formatted_number]);
?>
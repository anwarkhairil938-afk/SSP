<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Gunakan procedure
$query = "CALL generate_nomor_surat_procedure($year, @nomor_surat)";
mysqli_query($koneksi, $query);

$result = mysqli_query($koneksi, "SELECT @nomor_surat as nomor_surat");
$row = mysqli_fetch_assoc($result);

if ($row && $row['nomor_surat']) {
    echo json_encode(['success' => true, 'nomor_surat' => $row['nomor_surat']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal generate nomor surat']);
}
?>
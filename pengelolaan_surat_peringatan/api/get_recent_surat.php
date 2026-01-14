<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Hitung statistik
$total_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan"))['total'];
$pending_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'pending'"))['total'];
$surat_disetujui = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'approved'"))['total'];
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'aktif'"))['total'];

echo json_encode([
    'success' => true,
    'total_surat' => $total_surat,
    'pending_surat' => $pending_surat,
    'surat_disetujui' => $surat_disetujui,
    'total_mahasiswa' => $total_mahasiswa
]);
?>
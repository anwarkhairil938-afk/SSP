<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Hapus file upload terlebih dahulu
$upload_dir = "../uploads/surat_peringatan/$id/";
if (file_exists($upload_dir)) {
    array_map('unlink', glob("$upload_dir/*.*"));
    rmdir($upload_dir);
}

$query = "DELETE FROM surat_peringatan WHERE id = '$id'";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['success' => true, 'message' => 'Surat berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus surat: ' . mysqli_error($koneksi)]);
}
?>
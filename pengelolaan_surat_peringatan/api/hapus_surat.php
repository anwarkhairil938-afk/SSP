<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil ID surat dari POST
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID surat tidak ditemukan']);
    exit();
}

$surat_id = mysqli_real_escape_string($koneksi, $_POST['id']);

// Hapus lampiran terlebih dahulu (akan otomatis terhapus karena foreign key cascade)
$delete_lampiran_query = "DELETE FROM lampiran_surat WHERE surat_id = '$surat_id'";
mysqli_query($koneksi, $delete_lampiran_query);

// Hapus surat
$query = "DELETE FROM surat_peringatan WHERE id = '$surat_id'";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['success' => true, 'message' => 'Surat berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus surat: ' . mysqli_error($koneksi)]);
}

mysqli_close($koneksi);
?>
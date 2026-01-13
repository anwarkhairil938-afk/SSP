<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Hapus dari tabel mahasiswa jika ada
    $delete_mahasiswa = "DELETE FROM mahasiswa WHERE user_id = '$id'";
    mysqli_query($koneksi, $delete_mahasiswa);
    
    // Hapus surat peringatan yang terkait dengan mahasiswa ini
    $delete_surat = "DELETE FROM surat_peringatan WHERE mahasiswa_id IN (SELECT id FROM mahasiswa WHERE user_id = '$id')";
    mysqli_query($koneksi, $delete_surat);
    
    // Hapus dari tabel users
    $delete_user = "DELETE FROM users WHERE id = '$id'";
    if (!mysqli_query($koneksi, $delete_user)) {
        throw new Exception('Gagal menghapus user: ' . mysqli_error($koneksi));
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Akun berhasil dihapus']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
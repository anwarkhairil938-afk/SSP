<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Dapatkan user_id dari mahasiswa
$query = "SELECT user_id FROM mahasiswa WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $user_id = $row['user_id'];
    
    // Mulai transaksi
    mysqli_begin_transaction($koneksi);
    
    try {
        // Hapus dari tabel mahasiswa
        $delete_mahasiswa = "DELETE FROM mahasiswa WHERE id = '$id'";
        if (!mysqli_query($koneksi, $delete_mahasiswa)) {
            throw new Exception('Gagal menghapus mahasiswa: ' . mysqli_error($koneksi));
        }
        
        // Hapus dari tabel users
        $delete_user = "DELETE FROM users WHERE id = '$user_id'";
        if (!mysqli_query($koneksi, $delete_user)) {
            throw new Exception('Gagal menghapus user: ' . mysqli_error($koneksi));
        }
        
        // Hapus surat peringatan yang terkait
        $delete_surat = "DELETE FROM surat_peringatan WHERE mahasiswa_id = '$id'";
        mysqli_query($koneksi, $delete_surat);
        
        mysqli_commit($koneksi);
        echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil dihapus']);
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}
?>
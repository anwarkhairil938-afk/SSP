<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil ID mahasiswa dari POST
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID mahasiswa tidak ditemukan']);
    exit();
}

$mahasiswa_id = mysqli_real_escape_string($koneksi, $_POST['id']);

// Cari user_id
$find_user_query = "SELECT user_id FROM mahasiswa WHERE id = '$mahasiswa_id'";
$find_user_result = mysqli_query($koneksi, $find_user_query);
$mahasiswa_data = mysqli_fetch_assoc($find_user_result);

if (!$mahasiswa_data) {
    echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan']);
    exit();
}

$user_id = $mahasiswa_data['user_id'];

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Hapus dari tabel mahasiswa
    $delete_mahasiswa_query = "DELETE FROM mahasiswa WHERE id = '$mahasiswa_id'";
    if (!mysqli_query($koneksi, $delete_mahasiswa_query)) {
        throw new Exception('Gagal menghapus data mahasiswa: ' . mysqli_error($koneksi));
    }
    
    // Hapus dari tabel users (akan menghapus cascade jika ada foreign key)
    $delete_user_query = "DELETE FROM users WHERE id = '$user_id'";
    if (!mysqli_query($koneksi, $delete_user_query)) {
        throw new Exception('Gagal menghapus data user: ' . mysqli_error($koneksi));
    }
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil dihapus']);
    
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($koneksi);
?>
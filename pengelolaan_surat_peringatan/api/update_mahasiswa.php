<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil data dari POST
$mahasiswa_id = mysqli_real_escape_string($koneksi, $_POST['mahasiswa_id']);
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
$program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
$semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Cari user_id berdasarkan mahasiswa_id
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
    // Update tabel users
    $update_user_query = "UPDATE users SET 
                         username = '$username',
                         nama = '$nama',
                         nim = '$nim',
                         program_studi = '$program_studi',
                         semester = '$semester',
                         status = '$status'
                         WHERE id = '$user_id'";
    
    if (!mysqli_query($koneksi, $update_user_query)) {
        throw new Exception('Gagal mengupdate user: ' . mysqli_error($koneksi));
    }
    
    // Update tabel mahasiswa
    $update_mahasiswa_query = "UPDATE mahasiswa SET 
                              nama = '$nama',
                              nim = '$nim',
                              program_studi = '$program_studi',
                              semester = '$semester',
                              status = '$status'
                              WHERE id = '$mahasiswa_id'";
    
    if (!mysqli_query($koneksi, $update_mahasiswa_query)) {
        throw new Exception('Gagal mengupdate mahasiswa: ' . mysqli_error($koneksi));
    }
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    echo json_encode(['success' => true, 'message' => 'Data mahasiswa berhasil diperbarui']);
    
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($koneksi);
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ambil data dari POST
$mahasiswa_id = mysqli_real_escape_string($koneksi, $_POST['mahasiswa_id']);
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
$program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
$semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$role = mysqli_real_escape_string($koneksi, $_POST['role']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Cek apakah username sudah ada (kecuali untuk mahasiswa ini)
$check_query = "SELECT m.id FROM mahasiswa m 
                JOIN users u ON m.user_id = u.id 
                WHERE u.username = '$username' AND m.id != '$mahasiswa_id'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
    exit();
}

// Cek apakah NIM sudah ada (kecuali untuk mahasiswa ini)
$check_nim_query = "SELECT id FROM mahasiswa WHERE nim = '$nim' AND id != '$mahasiswa_id'";
$check_nim_result = mysqli_query($koneksi, $check_nim_query);

if (mysqli_num_rows($check_nim_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'NIM sudah terdaftar']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Dapatkan user_id dari mahasiswa
    $get_user_query = "SELECT user_id FROM mahasiswa WHERE id = '$mahasiswa_id'";
    $get_user_result = mysqli_query($koneksi, $get_user_query);
    $user_data = mysqli_fetch_assoc($get_user_result);
    $user_id = $user_data['user_id'];
    
    // Update tabel users
    $user_query = "UPDATE users SET 
                   username = '$username', 
                   nama = '$nama', 
                   role = '$role', 
                   status = '$status',
                   updated_at = NOW()
                   WHERE id = '$user_id'";
    
    if (!mysqli_query($koneksi, $user_query)) {
        throw new Exception('Gagal update user: ' . mysqli_error($koneksi));
    }
    
    // Update password jika diisi
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pass_query = "UPDATE users SET password = '$password' WHERE id = '$user_id'";
        
        if (!mysqli_query($koneksi, $pass_query)) {
            throw new Exception('Gagal update password: ' . mysqli_error($koneksi));
        }
    }
    
    // Update tabel mahasiswa
    $mahasiswa_query = "UPDATE mahasiswa SET 
                        nama = '$nama', 
                        nim = '$nim', 
                        program_studi = '$program_studi', 
                        semester = '$semester', 
                        status = '$status',
                        updated_at = NOW()
                        WHERE id = '$mahasiswa_id'";
    
    if (!mysqli_query($koneksi, $mahasiswa_query)) {
        throw new Exception('Gagal update mahasiswa: ' . mysqli_error($koneksi));
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil diperbarui']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
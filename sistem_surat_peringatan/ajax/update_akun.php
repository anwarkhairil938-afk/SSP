<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ambil data dari POST
$akun_id = mysqli_real_escape_string($koneksi, $_POST['akun_id']);
$username = mysqli_real_escape_string($koneksi, $_POST['usernameAkun']);
$nama = mysqli_real_escape_string($koneksi, $_POST['namaAkun']);
$role = mysqli_real_escape_string($koneksi, $_POST['roleAkun']);
$status = mysqli_real_escape_string($koneksi, $_POST['statusAkun']);
$nim = isset($_POST['nimAkun']) ? mysqli_real_escape_string($koneksi, $_POST['nimAkun']) : '';

// Cek apakah username sudah ada (kecuali untuk akun ini)
$check_query = "SELECT id FROM users WHERE username = '$username' AND id != '$akun_id'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Update tabel users
    $user_query = "UPDATE users SET 
                   username = '$username', 
                   nama = '$nama', 
                   role = '$role', 
                   status = '$status',
                   updated_at = NOW()
                   WHERE id = '$akun_id'";
    
    if (!mysqli_query($koneksi, $user_query)) {
        throw new Exception('Gagal update user: ' . mysqli_error($koneksi));
    }
    
    // Update password jika diisi
    if (isset($_POST['passwordAkun']) && !empty($_POST['passwordAkun'])) {
        $password = password_hash($_POST['passwordAkun'], PASSWORD_DEFAULT);
        $pass_query = "UPDATE users SET password = '$password' WHERE id = '$akun_id'";
        
        if (!mysqli_query($koneksi, $pass_query)) {
            throw new Exception('Gagal update password: ' . mysqli_error($koneksi));
        }
    }
    
    // Handle tabel mahasiswa
    if ($role == 'mahasiswa') {
        // Cek apakah sudah ada di tabel mahasiswa
        $check_mahasiswa = "SELECT id FROM mahasiswa WHERE user_id = '$akun_id'";
        $result_mahasiswa = mysqli_query($koneksi, $check_mahasiswa);
        
        if (mysqli_num_rows($result_mahasiswa) > 0) {
            // Update data mahasiswa
            $mahasiswa_query = "UPDATE mahasiswa SET 
                                nama = '$nama', 
                                nim = '$nim', 
                                status = '$status',
                                updated_at = NOW()
                                WHERE user_id = '$akun_id'";
            
            if (!mysqli_query($koneksi, $mahasiswa_query)) {
                throw new Exception('Gagal update mahasiswa: ' . mysqli_error($koneksi));
            }
        } else {
            // Insert ke tabel mahasiswa jika belum ada
            $mahasiswa_query = "INSERT INTO mahasiswa (user_id, nama, nim, program_studi, semester, status, created_at) 
                                VALUES ('$akun_id', '$nama', '$nim', 'Belum Ditentukan', 1, '$status', NOW())";
            
            if (!mysqli_query($koneksi, $mahasiswa_query)) {
                throw new Exception('Gagal menambahkan mahasiswa: ' . mysqli_error($koneksi));
            }
        }
    } else {
        // Jika role bukan mahasiswa, hapus dari tabel mahasiswa jika ada
        $delete_mahasiswa = "DELETE FROM mahasiswa WHERE user_id = '$akun_id'";
        mysqli_query($koneksi, $delete_mahasiswa);
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Akun berhasil diperbarui']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
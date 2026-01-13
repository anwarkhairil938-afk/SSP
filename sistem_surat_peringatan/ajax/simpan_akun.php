<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ambil data dari POST
$username = mysqli_real_escape_string($koneksi, $_POST['usernameAkun']);
$nama = mysqli_real_escape_string($koneksi, $_POST['namaAkun']);
$password = password_hash($_POST['passwordAkun'], PASSWORD_DEFAULT);
$role = mysqli_real_escape_string($koneksi, $_POST['roleAkun']);
$status = mysqli_real_escape_string($koneksi, $_POST['statusAkun']);
$nim = isset($_POST['nimAkun']) ? mysqli_real_escape_string($koneksi, $_POST['nimAkun']) : '';

// Cek apakah username sudah ada
$check_query = "SELECT id FROM users WHERE username = '$username'";
$check_result = mysqli_query($koneksi, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Insert ke tabel users
    $user_query = "INSERT INTO users (username, password, nama, role, status, created_at) 
                   VALUES ('$username', '$password', '$nama', '$role', '$status', NOW())";
    
    if (!mysqli_query($koneksi, $user_query)) {
        throw new Exception('Gagal menambahkan user: ' . mysqli_error($koneksi));
    }
    
    $user_id = mysqli_insert_id($koneksi);
    
    // Jika role adalah mahasiswa dan ada NIM, tambahkan ke tabel mahasiswa
    if ($role == 'mahasiswa' && !empty($nim)) {
        // Cek apakah NIM sudah ada
        $check_nim_query = "SELECT id FROM mahasiswa WHERE nim = '$nim'";
        $check_nim_result = mysqli_query($koneksi, $check_nim_query);
        
        if (mysqli_num_rows($check_nim_result) > 0) {
            throw new Exception('NIM sudah terdaftar');
        }
        
        $mahasiswa_query = "INSERT INTO mahasiswa (user_id, nama, nim, program_studi, semester, status, created_at) 
                            VALUES ('$user_id', '$nama', '$nim', 'Belum Ditentukan', 1, '$status', NOW())";
        
        if (!mysqli_query($koneksi, $mahasiswa_query)) {
            throw new Exception('Gagal menambahkan mahasiswa: ' . mysqli_error($koneksi));
        }
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Akun berhasil ditambahkan']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
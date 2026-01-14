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
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
$program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
$semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Validasi data
if (empty($nama) || empty($nim) || empty($program_studi) || empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

// Cek apakah NIM sudah ada
$check_nim_query = "SELECT id FROM mahasiswa WHERE nim = '$nim'";
$check_nim_result = mysqli_query($koneksi, $check_nim_query);
if (mysqli_num_rows($check_nim_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'NIM sudah terdaftar']);
    exit();
}

// Cek apakah username sudah ada
$check_username_query = "SELECT id FROM users WHERE username = '$username'";
$check_username_result = mysqli_query($koneksi, $check_username_query);
if (mysqli_num_rows($check_username_result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Insert ke tabel users
    $user_query = "INSERT INTO users (username, password, nama, role, nim, program_studi, semester, status) 
                  VALUES ('$username', '$password', '$nama', 'mahasiswa', '$nim', '$program_studi', '$semester', '$status')";
    
    if (!mysqli_query($koneksi, $user_query)) {
        throw new Exception('Gagal menambahkan user: ' . mysqli_error($koneksi));
    }
    
    $user_id = mysqli_insert_id($koneksi);
    
    // Insert ke tabel mahasiswa
    $mahasiswa_query = "INSERT INTO mahasiswa (nama, nim, program_studi, semester, status, user_id) 
                       VALUES ('$nama', '$nim', '$program_studi', '$semester', '$status', '$user_id')";
    
    if (!mysqli_query($koneksi, $mahasiswa_query)) {
        throw new Exception('Gagal menambahkan mahasiswa: ' . mysqli_error($koneksi));
    }
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil ditambahkan', 'id' => $user_id]);
    
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($koneksi);
?>
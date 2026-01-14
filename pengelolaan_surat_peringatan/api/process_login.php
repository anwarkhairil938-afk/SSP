<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

// Ambil data dari POST
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$remember = isset($_POST['remember']);

// Validasi input
if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit();
}

// Cek user di database
$query = "SELECT * FROM users WHERE username = ? AND role = ? AND status = 'aktif'";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ss", $username, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    // Verifikasi password (plain text untuk sementara)
    if ($user['password'] === $password) {
        // Update last login
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);
        
        // Set session
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        // Ambil data tambahan berdasarkan role
        if ($role === 'mahasiswa') {
            $query_mahasiswa = "SELECT id, nim, program_studi, semester FROM mahasiswa WHERE user_id = ?";
            $stmt_mahasiswa = mysqli_prepare($koneksi, $query_mahasiswa);
            mysqli_stmt_bind_param($stmt_mahasiswa, "i", $user['id']);
            mysqli_stmt_execute($stmt_mahasiswa);
            $result_mahasiswa = mysqli_stmt_get_result($stmt_mahasiswa);
            
            if ($mahasiswa = mysqli_fetch_assoc($result_mahasiswa)) {
                $_SESSION['mahasiswa_id'] = $mahasiswa['id'];
                $_SESSION['nim'] = $mahasiswa['nim'];
                $_SESSION['program_studi'] = $mahasiswa['program_studi'];
                $_SESSION['semester'] = $mahasiswa['semester'];
            }
        }
        
        // Set cookie jika remember me dicentang
        if ($remember) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/");
            setcookie('remember_role', $role, time() + (86400 * 30), "/");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login berhasil! Mengalihkan...',
            'role' => $role
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Password tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Username, password, atau peran tidak valid']);
}

mysqli_stmt_close($stmt);
?>
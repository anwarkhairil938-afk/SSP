<?php
session_start();
require_once "auth_koneksi/koneksi.php";

// Jika sudah login, redirect
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard_mahasiswa.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($nim) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek username unik
        $check_username = "SELECT id FROM users WHERE username = ?";
        $stmt_check = mysqli_prepare($koneksi, $check_username);
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Cek NIM unik
            $check_nim = "SELECT id FROM mahasiswa WHERE nim = ?";
            $stmt_nim = mysqli_prepare($koneksi, $check_nim);
            mysqli_stmt_bind_param($stmt_nim, "s", $nim);
            mysqli_stmt_execute($stmt_nim);
            mysqli_stmt_store_result($stmt_nim);
            
            if (mysqli_stmt_num_rows($stmt_nim) > 0) {
                $error = 'NIM sudah terdaftar!';
            } else {
                // Mulai transaksi
                mysqli_begin_transaction($koneksi);
                
                try {
                    // Insert ke users
                    $user_query = "INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'mahasiswa', 'aktif')";
                    $stmt_user = mysqli_prepare($koneksi, $user_query);
                    mysqli_stmt_bind_param($stmt_user, "sss", $username, $password, $nama);
                    
                    if (!mysqli_stmt_execute($stmt_user)) {
                        throw new Exception('Gagal membuat user');
                    }
                    
                    $user_id = mysqli_insert_id($koneksi);
                    
                    // Insert ke mahasiswa
                    $mahasiswa_query = "INSERT INTO mahasiswa (user_id, nama, nim, program_studi, semester, status) VALUES (?, ?, ?, 'Belum Ditentukan', 1, 'aktif')";
                    $stmt_mhs = mysqli_prepare($koneksi, $mahasiswa_query);
                    mysqli_stmt_bind_param($stmt_mhs, "iss", $user_id, $nama, $nim);
                    
                    if (!mysqli_stmt_execute($stmt_mhs)) {
                        throw new Exception('Gagal membuat data mahasiswa');
                    }
                    
                    mysqli_commit($koneksi);
                    $success = 'Pendaftaran berhasil! Silakan login.';
                    
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $error = $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Surat Peringatan Polibatam</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="logo">
                <h1>Daftar Akun Baru</h1>
                <p>Politeknik Negeri Batam</p>
            </div>
            
            <?php if ($error): ?>
                <div class="php-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="php-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="register" value="1">
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" name="nim" class="form-control" placeholder="Masukkan NIM" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Daftar</button>
                
                <div class="form-navigation">
                    <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                    <p style="margin-top: 10px;">
                        <a href="landing_page.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            <span>Kembali ke Halaman Utama</span>
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
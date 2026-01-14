<?php
session_start();
require_once "auth_koneksi/koneksi.php";

// Cek jika sudah login, redirect ke dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard_admin.php');
        exit;
    } elseif ($_SESSION['role'] === 'mahasiswa') {
        header('Location: dashboard_mahasiswa.php');
        exit;
    }
}

// Proses login
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    
    // Validasi input
    if (empty($username) || empty($password) || empty($role)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Authentikasi user dari database
        $query = "SELECT * FROM users WHERE username = ? AND role = ? AND status = 'aktif'";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // NOTE: Password disimpan plain text untuk sementara
            // Di production, gunakan password_hash() dan password_verify()
            if ($user['password'] === $password) {
                // Set session
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                // Set cookie jika remember me dicentang
                if (isset($_POST['remember'])) {
                    setcookie('remember_user', $username, time() + (86400 * 30), "/");
                }
                
                // Redirect berdasarkan role
                if ($role === 'admin') {
                    header('Location: dashboard_admin.php');
                    exit;
                } else {
                    header('Location: dashboard_mahasiswa.php');
                    exit;
                }
            } else {
                $error = 'Password tidak valid!';
            }
        } else {
            $error = 'Username, password, atau peran tidak valid!';
        }
        mysqli_stmt_close($stmt);
    }
}

// Cek apakah wallpaper ada
$wallpaperPath = 'images/WALLPAPER POLIBATAM.jpg';
$wallpaperExists = file_exists($wallpaperPath);

// Cek apakah logo ada
$logoPath = 'images/POLIBATAM LOGO.png';
$logoExists = file_exists($logoPath);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Surat Peringatan - Polibatam</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        /* Style untuk logo gambar - SEDERHANA */
        .logo-image {
            display: flex;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .logo-img {
            max-width: 120px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            object-fit: contain;
            /* Hapus animasi floating */
        }
        
        /* Style untuk placeholder jika logo tidak ditemukan */
        .logo-placeholder {
            background: linear-gradient(135deg, #1a73e8, #4caf50);
            border-radius: 10px;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* WALLPAPER FULLSCREEN tanpa distorsi */
        .login-page {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        <?php if ($wallpaperExists): ?>
        .login-page::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo $wallpaperPath; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            z-index: -2;
        }
        
        .login-page::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8));
            z-index: -1;
        }
        <?php else: ?>
        .login-page::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a73e8, #333, #4caf50);
            z-index: -2;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="login-page" id="loginPage">
        <div class="login-container">
            <div class="logo">
                <?php if ($logoExists): ?>
                <!-- Logo gambar eksternal -->
                <div class="logo-image">
                    <img src="<?php echo $logoPath; ?>" alt="Logo Polibatam" class="logo-img">
                </div>
                <?php else: ?>
                <!-- Placeholder jika logo tidak ditemukan -->
                <div class="logo-placeholder">
                    <i class="fas fa-university"></i>
                </div>
                <?php endif; ?>
                <h1>Sistem Surat Peringatan</h1>
                <p>Politeknik Negeri Batam</p>
            </div>
            
            <h2>Masuk ke Akun Anda</h2>
            
            <!-- Tampilkan pesan error/success dari PHP -->
            <?php if (!empty($error)): ?>
                <div class="php-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="php-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Masukkan username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i>
                        Masuk Sebagai *
                    </label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Pilih Peran</option>
                        <option value="mahasiswa" <?php echo (isset($_POST['role']) && $_POST['role'] === 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <div id="successMessage" class="success-message" style="display: none;"></div>
                <div id="loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Memproses login...
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Masuk</span>
                </button>
                
                <div class="form-navigation">
                    <p>Belum punya akun? <a href="register.php" id="showRegisterLink">Daftar di sini</a></p>
                    <p style="margin-top: 10px;">
                        <a href="landing_page.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            <span>Kembali ke Halaman Utama</span>
                        </a>
                    </p>
                </div>
            </form>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Politeknik Negeri Batam</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            
            // Cek apakah pesan error dari PHP ada
            const phpError = document.querySelector('.php-error');
            if (phpError) {
                setTimeout(() => {
                    phpError.style.transition = 'opacity 0.5s';
                    phpError.style.opacity = '0';
                    setTimeout(() => phpError.remove(), 500);
                }, 5000);
            }
            
            // Setup form submission untuk PHP
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // Validasi client-side
                    const username = document.getElementById('username').value.trim();
                    const password = document.getElementById('password').value.trim();
                    const role = document.getElementById('role').value;
                    
                    if (!username || !password || !role) {
                        e.preventDefault();
                        showMessage('error', 'Semua field harus diisi!');
                        return false;
                    }
                    
                    // Tampilkan loading
                    showMessage('loading', '');
                    return true; // Lanjutkan submit ke server
                });
            }
            
            // Fungsi untuk menampilkan pesan
            function showMessage(type, message) {
                const errorMessage = document.getElementById('errorMessage');
                const successMessage = document.getElementById('successMessage');
                const loadingElement = document.getElementById('loading');
                const loginBtn = document.getElementById('loginBtn');
                
                // Sembunyikan semua pesan terlebih dahulu
                if (errorMessage) errorMessage.style.display = 'none';
                if (successMessage) successMessage.style.display = 'none';
                if (loadingElement) loadingElement.style.display = 'none';
                
                // Tampilkan pesan sesuai jenis
                if (type === 'error') {
                    if (errorMessage) {
                        errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> <span>${message}</span>`;
                        errorMessage.style.display = 'flex';
                    }
                } else if (type === 'success') {
                    if (successMessage) {
                        successMessage.innerHTML = `<i class="fas fa-check-circle"></i> <span>${message}</span>`;
                        successMessage.style.display = 'flex';
                    }
                } else if (type === 'loading') {
                    if (loadingElement) {
                        loadingElement.style.display = 'flex';
                    }
                }
                
                // Reset tombol login
                if (loginBtn) {
                    loginBtn.style.display = type === 'loading' ? 'none' : 'flex';
                }
            }
            
            // Toggle password visibility
            const togglePasswordBtn = document.getElementById('togglePassword');
            if (togglePasswordBtn) {
                togglePasswordBtn.addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        passwordInput.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                });
            }
            
            // Auto-fill dari cookie jika ada
            const rememberCookie = getCookie('remember_user');
            if (rememberCookie && document.getElementById('username')) {
                document.getElementById('username').value = rememberCookie;
                if (document.getElementById('remember')) {
                    document.getElementById('remember').checked = true;
                }
            }
            
            // Cek error logo (lebih sederhana)
            const logoImg = document.querySelector('.logo-img');
            if (logoImg) {
                logoImg.onerror = function() {
                    const logoContainer = this.parentElement;
                    if (logoContainer) {
                        logoContainer.innerHTML = `
                            <div class="logo-placeholder">
                                <i class="fas fa-university"></i>
                            </div>
                        `;
                    }
                };
            }
        });
        
        // Fungsi untuk mengambil cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
    </script>
</body>
</html>
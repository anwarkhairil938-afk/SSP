<?php
session_start();

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard_admin.php');
        exit();
    } elseif ($_SESSION['role'] === 'mahasiswa') {
        header('Location: dashboard_mahasiswa.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Surat Peringatan - Polibatam</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="VARIABEL.CSS">
    <link rel="stylesheet" href="MAIN.CSS">
    <link rel="stylesheet" href="ANIMATION.CSS">
    <link rel="stylesheet" href="RESPONSIVE.CSS">
</head>
<body>
    <!-- Header & Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="container nav-container">
                <div class="logo">
                    <img src="images/logo.png" alt="Logo Polibatam" id="logo-image">
                    <div class="logo-text">
                        <h2>Sistem Surat Peringatan</h2>
                        <p>Politeknik Negeri Batam</p>
                    </div>
                </div>
                <ul class="nav-menu">
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#features">Fitur</a></li>
                    <li><a href="#team">Tim</a></li>
                    <li><a href="#access">Akses</a></li>
                    <li><a href="login.php" class="login-btn" id="login-btn">Masuk</a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <h1>Kelola Surat Peringatan dengan Mudah dan Efisien</h1>
                <p>Platform digital untuk mengelola surat peringatan bagi mahasiswa dan staff akademik di lingkungan Politeknik Negeri Batam</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary" id="hero-login-btn">Masuk ke Sistem</a>
                    <a href="#features" class="btn btn-secondary">Pelajari Fitur</a>
                </div>
            </div>
            <div class="hero-image">
                <i class="fas fa-file-contract"></i>
                <div class="floating-icons">
                    <div class="floating-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="floating-icon"><i class="fas fa-user-cog"></i></div>
                    <div class="floating-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="floating-icon"><i class="fas fa-database"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section (NEW LAYOUT) -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title"><span>Fitur Utama Sistem</span></h2>
            
            <div class="features-grid">
                <!-- Fitur 1: Surat Digital -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                    </div>
                    <h3>Surat Digital</h3>
                    <p>Buat dan kelola surat peringatan dalam format digital dengan template yang rapi dan profesional</p>
                </div>
                
                <!-- Fitur 2: Analitik Data -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <h3>Analitik Data</h3>
                    <p>Pantau statistik dan trend pelanggaran dengan visualisasi data yang jelas</p>
                </div>
                
                <!-- Fitur 3: Manajemen Anggota -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3>Manajemen Anggota</h3>
                    <p>Kelola data mahasiswa dan staff dengan sistem terpusat dan terintegrasi</p>
                </div>
                
                <!-- Fitur 4: Keamanan Terjamin -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <h3>Keamanan Terjamin</h3>
                    <p>Sistem keamanan berlapis untuk melindungi data dan privasi pengguna</p>
                </div>
                
                <!-- Fitur 5: Arsip Digital -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-archive"></i>
                        </div>
                    </div>
                    <h3>Arsip Digital</h3>
                    <p>Simpan dan akses arsip surat secara digital dengan pencarian yang cepat</p>
                </div>
                
                <!-- Fitur 6: Responsif & Mobile -->
                <div class="feature-card">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon-bg"></div>
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                    </div>
                    <h3>Responsif & Mobile</h3>
                    <p>Akses sistem dari berbagai perangkat dengan interface yang user-friendly</p>
                </div>
            </div>
            
            <div class="features-bottom">
                <h3>Sistem Terintegrasi yang Efisien</h3>
              <p>Dengan fitur-fitur canggih di atas, sistem ini akan membantu Politeknik Negeri Batam dalam mengelola surat peringatan secara lebih efektif, transparan, dan efisien.</p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="team">
        <div class="container">
            <h2 class="section-title"><span>Tim Pengembang</span></h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-photo">
                        <img src="images/KHAIRIL.jpg" alt="M. Khairil Candra">
                    </div>
                    <h3 class="team-name">M. Khairil Candra</h3>
                    <p class="team-role">Full Stack Developer</p>
                    <p class="team-description">Bertanggung jawab dalam pengembangan backend dan frontend sistem.</p>
                </div>
                <div class="team-card">
                    <div class="team-photo">
                        <img src="images/YOGA.JPG" alt="Yoga Putra Agusetiawan">
                    </div>
                    <h3 class="team-name">Yoga Putra Agusetiawan</h3>
                    <p class="team-role">UI/UX Designer</p>
                    <p class="team-description">null.</p>
                </div>
                <div class="team-card">
                    <div class="team-photo">
                        <img src="images/DIVANI.jpg" alt="Divani Putri Olivia Hutagaol">
                    </div>
                    <h3 class="team-name">Divani Putri Olivia Hutagaol</h3>
                    <p class="team-role">System Analyst</p>
                    <p class="team-description">null.</p>
                </div>
                <div class="team-card">
                    <div class="team-photo">
                        <img src="images/NITA.jpg" alt="Qoonita Novia Damayanti">
                    </div>
                    <h3 class="team-name">Qoonita Novia Damayanti</h3>
                    <p class="team-role">Database Administrator</p>
                    <p class="team-description">null.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access Section -->
    <section id="access" class="quick-access">
        <div class="container">
            <h2 class="section-title"><span>Akses Cepat</span></h2>
            <div class="access-grid">
                <div class="access-card">
                    <div class="access-icon-wrapper">
                        <div class="access-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <h3>Mahasiswa</h3>
                    <p>Lihat riwayat surat peringatan dan status akademik Anda.</p>
                    <a href="login.php" class="access-btn login-as" data-role="mahasiswa">Masuk sebagai Mahasiswa</a>
                </div>
                <div class="access-card">
                    <div class="access-icon-wrapper">
                        <div class="access-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                    </div>
                    <h3>Admin</h3>
                    <p>Kelola seluruh sistem dan akses data lengkap surat peringatan.</p>
                    <a href="login.php" class="access-btn login-as" data-role="admin">Masuk sebagai Admin</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Logo Polibatam" id="footer-logo-image">
                    <div class="footer-text">
                        <h3>Sistem Surat Peringatan</h3>
                        <p>Politeknik Negeri Batam</p>
                    </div>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Tautan Cepat</h4>
                        <ul>
                            <li><a href="#home">Beranda</a></li>
                            <li><a href="#features">Fitur</a></li>
                            <li><a href="#team">Tim</a></li>
                            <li><a href="#access">Akses</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Kontak</h4>
                        <ul>
                            <li><i class="fas fa-map-marker-alt"></i> Jl. Ahmad Yani, Batam</li>
                            <li><i class="fas fa-phone"></i> (0778) 469858</li>
                            <li><i class="fas fa-envelope"></i> info@polibatam.ac.id</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Sistem Surat Peringatan Polibatam. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="MAIN.JS"></script>
    <script src="ANIMATION.JS"></script>
</body>
</html>
<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Cek apakah user adalah mahasiswa
if ($_SESSION['role'] !== 'mahasiswa') {
    header('Location: login.php');
    exit;
}

// Data user dari session
$nama = $_SESSION['nama'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Hapus semua data session
    $_SESSION = array();
    
    // Hapus session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hancurkan session
    session_destroy();
    
    // Redirect langsung ke login.php
    header('Location: login.php');
    exit;
}

// Data sampel untuk demo
$mahasiswaData = [
    'nama' => $nama,
    'username' => $username,
    'nim' => '3312511014',
    'programStudi' => 'Teknik Informatika',
    'semester' => '5',
    'poinAkademik' => 85
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #4a148c, #7b1fa2);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            color: #4a148c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .dashboard-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .welcome-card h1 {
            color: #4a148c;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            color: #4a148c;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #4a148c;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div>
            <h1>Dashboard Mahasiswa</h1>
            <p>Sistem Surat Peringatan - Politeknik Negeri Batam</p>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($nama, 0, 1)); ?>
            </div>
            <div>
                <strong><?php echo htmlspecialchars($nama); ?></strong>
                <p style="font-size: 12px; opacity: 0.8;">NIM: <?php echo $mahasiswaData['nim']; ?></p>
            </div>
            <!-- TOMBOL LOGOUT LANGSUNG KE LOGIN.PHP -->
            <a href="?action=logout" class="logout-btn">
                Logout
            </a>
        </div>
    </div>
    
    <!-- DASHBOARD CONTENT -->
    <div class="dashboard-content">
        <div class="welcome-card">
            <h1>Selamat Datang, <?php echo explode(' ', $nama)[0]; ?>!</h1>
            <p>Ini adalah dashboard mahasiswa Sistem Surat Peringatan.</p>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>Data Mahasiswa</h3>
                    <p><strong>NIM:</strong> <?php echo $mahasiswaData['nim']; ?></p>
                    <p><strong>Program Studi:</strong> <?php echo $mahasiswaData['programStudi']; ?></p>
                    <p><strong>Semester:</strong> <?php echo $mahasiswaData['semester']; ?></p>
                    <p><strong>Poin Akademik:</strong> <?php echo $mahasiswaData['poinAkademik']; ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Informasi Sistem</h3>
                    <p>Username: <?php echo htmlspecialchars($username); ?></p>
                    <p>Role: <?php echo ucfirst($role); ?></p>
                    <p>Status: Aktif</p>
                </div>
            </div>
            
            <!-- TOMBOL BACK LANGSUNG KE LOGIN.PHP -->
            <a href="?action=logout" class="back-link">
                ← Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>
<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

// Include koneksi database
require_once "auth_koneksi/auth.php";
require_once "auth_koneksi/koneksi.php";

// Ambil data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$current_user = mysqli_fetch_assoc($result_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Dashboard Admin - Sistem Surat Peringatan Polibatam</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Additional inline styles */
        .no-data {
            text-align: center;
            color: var(--text-secondary);
            padding: 40px;
            font-style: italic;
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: var(--primary);
        }
        
        .loading i {
            margin-right: 10px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Tabs Styles */
        .tabs-container {
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .tabs-header {
            display: flex;
            background: var(--light);
            border-bottom: 1px solid var(--border-color);
        }
        
        .tab-btn {
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-btn:hover {
            background: var(--bg-secondary);
            color: var(--primary);
        }
        
        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: var(--bg-secondary);
        }
        
        .tab-content {
            display: none;
            padding: 25px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* File Upload Styles */
        .file-upload-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .file-upload-button {
            position: relative;
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            padding: 15px;
            transition: all 0.3s;
            background: var(--light-secondary);
        }
        
        .file-upload-button:hover {
            border-color: var(--primary);
            background: var(--light);
        }
        
        .file-preview-container {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 15px;
            background: var(--light-secondary);
            margin-top: 15px;
        }
        
        .preview-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .preview-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .preview-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .preview-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light);
            border-radius: 6px;
            color: var(--primary);
            font-size: 18px;
        }
        
        .preview-details h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .preview-details p {
            margin: 0;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .preview-actions {
            display: flex;
            gap: 5px;
        }
        
        .preview-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .preview-action-btn:hover {
            background: var(--light);
            color: var(--danger);
        }
        
        .file-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-uploaded {
            background: rgba(80, 200, 120, 0.1);
            color: var(--success);
        }
        
        .status-uploading {
            background: rgba(255, 165, 0, 0.1);
            color: var(--warning);
        }
        
        .status-error {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger);
        }
        
        /* Password container */
        .password-container {
            position: relative;
        }
        
        .password-container input {
            padding-right: 40px;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
        }
        
        /* System info */
        .system-info {
            background: var(--light);
            padding: 20px;
            border-radius: var(--border-radius);
        }
        
        .system-info p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        
        .system-info strong {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <!-- SIDEBAR OVERLAY FOR MOBILE -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- DASHBOARD -->
    <div class="dashboard-page" id="dashboardPage">
        <div class="app-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h2>Sistem Surat Peringatan</h2>
                    <p>Polibatam</p>
                </div>
                
                <ul class="sidebar-nav">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" id="navDashboard">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="navRiwayat">
                            <i class="fas fa-history"></i>
                            <span>Riwayat Surat</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="navArsipLink">
                            <i class="fas fa-archive"></i>
                            <span>Arsip Surat</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="navDataMahasiswaLink">
                            <i class="fas fa-user-graduate"></i>
                            <span>Data Mahasiswa</span>
                        </a>
                    </li>
                    <!-- Menu Pengaturan -->
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="navPengaturan">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                    </li>
                </ul>
                
                <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <button class="btn btn-secondary" id="logoutBtn" style="width: 100%; background: rgba(255,255,255,0.1); color: white;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="main-header">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div style="display: flex; flex-direction: column;">
                            <span id="breadcrumb">Dashboard</span>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; gap: 10px;">
                                <span id="currentDate"><?php echo date('l, d F Y'); ?></span>
                                <span id="currentTime"><?php echo date('H:i:s'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="header-right">
                        <button class="header-btn" id="themeToggle">
                            <i class="fas fa-moon"></i>
                        </button>
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php 
                                $initial = isset($current_user['nama']) ? strtoupper(substr($current_user['nama'], 0, 1)) : 'A';
                                echo $initial;
                                ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name" id="headerUserName">
                                    <?php echo isset($current_user['nama']) ? htmlspecialchars($current_user['nama']) : 'Admin Sistem'; ?>
                                </div>
                                <div class="user-role" id="headerUserRole">
                                    <?php 
                                    if (isset($current_user['role'])) {
                                        echo ($current_user['role'] === 'admin') ? 'Administrator' : 'Mahasiswa';
                                    } else {
                                        echo 'Administrator';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-content">
                    <!-- Dashboard Utama -->
                    <div id="dashboardMain">
                        <!-- Welcome Section -->
                        <div class="welcome-card">
                            <h1 id="welcomeTitle">
                                Selamat Datang, <?php echo htmlspecialchars($current_user['nama']); ?>!
                            </h1>
                            <p id="welcomeText">
                                Anda dapat membuat, mengelola, dan melacak semua surat peringatan.
                            </p>
                            <div class="stats-container">
                                <?php
                                // Hitung statistik
                                $total_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan"))['total'];
                                $pending_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'pending'"))['total'];
                                $total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'aktif'"))['total'];
                                ?>
                                <div class="stat-item">
                                    <div class="number" id="statLetters"><?php echo $total_surat; ?></div>
                                    <div>Surat Peringatan</div>
                                </div>
                                <div class="stat-item">
                                    <div class="number" id="statPending"><?php echo $pending_surat; ?></div>
                                    <div>Menunggu Tindakan</div>
                                </div>
                                <div class="stat-item">
                                    <div class="number" id="statUsers"><?php echo $total_mahasiswa; ?></div>
                                    <div>Mahasiswa Aktif</div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Section -->
                        <div class="stats-grid">
                            <?php
                            $surat_disetujui = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'approved'"))['total'];
                            ?>
                            <div class="stat-card">
                                <div class="stat-value" id="statTotalSurat"><?php echo $total_surat; ?></div>
                                <div class="stat-label">Surat Dibuat</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-value" id="statSuratDisetujui"><?php echo $surat_disetujui; ?></div>
                                <div class="stat-label">Surat Disetujui</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-value" id="statMenunggu"><?php echo $pending_surat; ?></div>
                                <div class="stat-label">Menunggu Persetujuan</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-value" id="statTotalMahasiswa"><?php echo $total_mahasiswa; ?></div>
                                <div class="stat-label">Mahasiswa Aktif</div>
                            </div>
                        </div>
                        
                        <!-- Content Section -->
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-tasks"></i> Tindakan Cepat</h3>
                            </div>
                            <div class="card-body">
                                <div class="action-grid">
                                    <button class="action-btn create" id="btnBuatSurat">
                                        <i class="fas fa-plus"></i>
                                        <span>Buat Surat Baru</span>
                                    </button>
                                    <button class="action-btn template" id="btnTemplateSurat">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Template Surat</span>
                                    </button>
                                    <button class="action-btn view" id="btnLihatSurat">
                                        <i class="fas fa-eye"></i>
                                        <span>Lihat Surat</span>
                                    </button>
                                    <button class="action-btn history" id="btnRiwayat">
                                        <i class="fas fa-history"></i>
                                        <span>Riwayat Surat</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-clock"></i> Surat Terbaru</h3>
                                <button class="btn btn-primary btn-sm" id="btnRefreshSurat">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <ul class="letters-list" id="suratTerbaruList">
                                    <?php
                                    // Ambil 5 surat terbaru
                                    $query_surat = "SELECT s.*, m.nama as nama_mahasiswa, m.nim 
                                                   FROM surat_peringatan s 
                                                   JOIN mahasiswa m ON s.mahasiswa_id = m.id 
                                                   ORDER BY s.created_at DESC LIMIT 5";
                                    $result_surat = mysqli_query($koneksi, $query_surat);
                                    
                                    if (mysqli_num_rows($result_surat) > 0) {
                                        while ($surat = mysqli_fetch_assoc($result_surat)) {
                                            $status_class = $surat['status'] == 'approved' ? 'status-approved' : 
                                                          ($surat['status'] == 'rejected' ? 'status-rejected' : 'status-pending');
                                            $status_text = $surat['status'] == 'approved' ? 'Disetujui' : 
                                                         ($surat['status'] == 'rejected' ? 'Ditolak' : 'Menunggu');
                                            ?>
                                            <li class="letter-item" onclick="tampilkanSurat(<?php echo $surat['id']; ?>)">
                                                <div>
                                                    <div class="letter-title"><?php echo htmlspecialchars($surat['nomor_surat']); ?></div>
                                                    <div class="letter-meta"><?php echo htmlspecialchars($surat['nama_mahasiswa']) . ' • ' . $surat['nim']; ?></div>
                                                </div>
                                                <span class="letter-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </li>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <li class="letter-item">
                                            <div style="text-align: center; padding: 20px; color: var(--text-secondary);">
                                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                                <p>Belum ada surat peringatan</p>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Halaman Riwayat -->
                    <div id="riwayatPage" style="display: none;">
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-history"></i> Riwayat Surat Peringatan</h3>
                                <div>
                                    <button class="btn btn-primary" id="btnTambahSuratRiwayat">
                                        <i class="fas fa-plus"></i> Tambah Surat
                                    </button>
                                    <button class="btn btn-secondary" id="kembaliDariRiwayat">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- PENCARIAN DAN FILTER -->
                                <div class="search-filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="cariNamaRiwayat">Cari Nama/NIM</label>
                                            <input type="text" id="cariNamaRiwayat" class="form-control" placeholder="Masukkan nama atau NIM...">
                                        </div>
                                        <div class="filter-group">
                                            <label for="filterJenisRiwayat">Filter Jenis</label>
                                            <select id="filterJenisRiwayat" class="form-control">
                                                <option value="">Semua Jenis</option>
                                                <option value="akademik">Pelanggaran Akademik</option>
                                                <option value="etika">Pelanggaran Etika</option>
                                                <option value="administrasi">Pelanggaran Administrasi</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="filterStatusRiwayat">Filter Status</label>
                                            <select id="filterStatusRiwayat" class="form-control">
                                                <option value="">Semua Status</option>
                                                <option value="pending">Menunggu</option>
                                                <option value="approved">Disetujui</option>
                                                <option value="rejected">Ditolak</option>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="btnCariRiwayat">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                            <button class="btn btn-secondary" id="btnResetRiwayat">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="riwayatTableContainer">
                                    <!-- Data akan diload via AJAX -->
                                    <div class="loading">
                                        <i class="fas fa-spinner"></i> Memuat data...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Halaman Arsip -->
                    <div id="arsipPage" style="display: none;">
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-archive"></i> Arsip Surat Peringatan</h3>
                                <div>
                                    <button class="btn btn-secondary" id="kembaliDariArsip">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- PENCARIAN DAN FILTER -->
                                <div class="search-filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="cariNamaArsip">Cari Nama Mahasiswa</label>
                                            <input type="text" id="cariNamaArsip" class="form-control" placeholder="Masukkan nama mahasiswa...">
                                        </div>
                                        <div class="filter-group">
                                            <label for="cariNIMArsip">Cari NIM</label>
                                            <input type="text" id="cariNIMArsip" class="form-control" placeholder="Masukkan NIM...">
                                        </div>
                                        <div class="filter-group">
                                            <label for="filterJenisArsip">Filter Jenis SP</label>
                                            <select id="filterJenisArsip" class="form-control">
                                                <option value="">Semua Jenis</option>
                                                <option value="akademik">Pelanggaran Akademik</option>
                                                <option value="etika">Pelanggaran Etika</option>
                                                <option value="administrasi">Pelanggaran Administrasi</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="btnCariArsip">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                            <button class="btn btn-secondary" id="btnResetArsip">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="arsipTableContainer">
                                    <!-- Data akan diload via AJAX -->
                                    <div class="loading">
                                        <i class="fas fa-spinner"></i> Memuat data...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Halaman Data Mahasiswa -->
                    <div id="dataMahasiswaPage" style="display: none;">
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-graduate"></i> Data Mahasiswa</h3>
                                <div>
                                    <button class="btn btn-primary" id="btnTambahMahasiswa">
                                        <i class="fas fa-plus"></i> Tambah Mahasiswa
                                    </button>
                                    <button class="btn btn-secondary" id="kembaliDariMahasiswa">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- PENCARIAN -->
                                <div class="search-filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="cariNamaMahasiswa">Cari Nama Mahasiswa</label>
                                            <input type="text" id="cariNamaMahasiswa" class="form-control" placeholder="Masukkan nama mahasiswa...">
                                        </div>
                                        <div class="filter-group">
                                            <label for="cariNIMMahasiswa">Cari NIM</label>
                                            <input type="text" id="cariNIMMahasiswa" class="form-control" placeholder="Masukkan NIM...">
                                        </div>
                                        <div class="filter-group">
                                            <label for="filterProdiMahasiswa">Filter Program Studi</label>
                                            <select id="filterProdiMahasiswa" class="form-control">
                                                <option value="">Semua Program Studi</option>
                                                <option value="Teknik Informatika">Teknik Informatika</option>
                                                <option value="Sistem Informasi">Sistem Informasi</option>
                                                <option value="Teknik Elektro">Teknik Elektro</option>
                                                <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="btnCariMahasiswa">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                            <button class="btn btn-secondary" id="btnResetMahasiswa">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="mahasiswaTableContainer">
                                    <!-- Data akan diload via AJAX -->
                                    <div class="loading">
                                        <i class="fas fa-spinner"></i> Memuat data...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Halaman Pengaturan -->
                    <div id="pengaturanPage" style="display: none;">
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-cog"></i> Pengaturan Sistem</h3>
                                <div>
                                    <button class="btn btn-secondary" id="kembaliDariPengaturan">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Tabs untuk Pengaturan -->
                                <div class="tabs-container">
                                    <div class="tabs-header">
                                        <button class="tab-btn active" data-tab="pengaturan-umum">Informasi Aplikasi</button>
                                        <button class="tab-btn" data-tab="pengaturan-akun">Kelola Akun Pengguna</button>
                                        <button class="tab-btn" data-tab="pengaturan-sistem">Sistem & Backup</button>
                                    </div>
                                    
                                    <!-- Tab 1: Informasi Aplikasi -->
                                    <div class="tab-content active" id="pengaturan-umum">
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="fas fa-info-circle"></i> Informasi Aplikasi
                                            </div>
                                            <form id="formPengaturanUmum">
                                                <div class="form-group">
                                                    <label for="namaAplikasi">Nama Aplikasi</label>
                                                    <input type="text" id="namaAplikasi" class="form-control" value="Sistem Surat Peringatan Polibatam">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="versiAplikasi">Versi Aplikasi</label>
                                                    <input type="text" id="versiAplikasi" class="form-control" value="1.0.0">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="namaInstitusi">Nama Institusi</label>
                                                    <input type="text" id="namaInstitusi" class="form-control" value="Politeknik Negeri Batam">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="alamatInstitusi">Alamat Institusi</label>
                                                    <textarea id="alamatInstitusi" class="form-control" rows="3">Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461</textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="telpInstitusi">Telepon Institusi</label>
                                                    <input type="text" id="telpInstitusi" class="form-control" value="(0778) 469858">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="emailInstitusi">Email Institusi</label>
                                                    <input type="email" id="emailInstitusi" class="form-control" value="polibatam@polibatam.ac.id">
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="logoAplikasi">Logo Aplikasi</label>
                                                    <div class="file-upload">
                                                        <input type="file" id="logoAplikasi" class="file-input" accept=".png,.jpg,.jpeg">
                                                        <label for="logoAplikasi" class="file-label">
                                                            <i class="fas fa-cloud-upload-alt"></i>
                                                            <div class="file-info">
                                                                <h5>Upload Logo Baru</h5>
                                                                <p>Format PNG, JPG (Maks. 2MB)</p>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <div class="logo-preview" id="logoPreview">
                                                        <img src="" alt="Logo Preview" style="max-width: 150px; max-height: 150px; display: none;">
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary" id="simpanPengaturanUmum">
                                                        <i class="fas fa-save"></i> Simpan Pengaturan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab 2: Kelola Akun Pengguna -->
                                    <div class="tab-content" id="pengaturan-akun">
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="fas fa-users-cog"></i> Kelola Akun Pengguna
                                            </div>
                                            
                                            <!-- Daftar Akun -->
                                            <div class="table-container" style="overflow-x: auto;">
                                                <table class="arsip-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Username</th>
                                                            <th>Nama</th>
                                                            <th>Peran</th>
                                                            <th>Status</th>
                                                            <th>Terakhir Login</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="akunTableBody">
                                                        <tr>
                                                            <td colspan="6" style="text-align: center; padding: 40px;" class="loading">
                                                                <i class="fas fa-spinner"></i> Memuat data...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Form Tambah/Edit Akun -->
                                            <div class="form-section" style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                                                <div class="form-section-title">
                                                    <i class="fas fa-user-plus"></i> Tambah/Edit Akun
                                                </div>
                                                
                                                <form id="formTambahAkun">
                                                    <input type="hidden" id="akun_id" value="">
                                                    <div class="form-row">
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="usernameAkun">Username *</label>
                                                                <input type="text" id="usernameAkun" class="form-control" placeholder="Masukkan username" required>
                                                            </div>
                                                        </div>
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="namaAkun">Nama Lengkap *</label>
                                                                <input type="text" id="namaAkun" class="form-control" placeholder="Masukkan nama lengkap" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="passwordAkun">Password *</label>
                                                                <div class="password-container">
                                                                    <input type="password" id="passwordAkun" class="form-control" placeholder="Masukkan password" required>
                                                                    <button type="button" class="toggle-password" id="togglePasswordAkun">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="roleAkun">Peran *</label>
                                                                <select id="roleAkun" class="form-control" required>
                                                                    <option value="">Pilih Peran</option>
                                                                    <option value="admin">Administrator</option>
                                                                    <option value="mahasiswa">Mahasiswa</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="statusAkun">Status *</label>
                                                                <select id="statusAkun" class="form-control" required>
                                                                    <option value="aktif">Aktif</option>
                                                                    <option value="nonaktif">Nonaktif</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-col">
                                                            <div class="form-group">
                                                                <label for="nimAkun">NIM (untuk Mahasiswa)</label>
                                                                <input type="text" id="nimAkun" class="form-control" placeholder="Masukkan NIM">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <button type="button" class="btn btn-primary" id="btnResetFormAkun">
                                                            <i class="fas fa-redo"></i> Reset Form
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="btnSimpanAkun">
                                                            <i class="fas fa-save"></i> Simpan Akun
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab 3: Sistem & Backup -->
                                    <div class="tab-content" id="pengaturan-sistem">
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="fas fa-database"></i> Sistem & Backup
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Backup Database</label>
                                                <p style="color: var(--text-secondary); margin-bottom: 15px;">
                                                    Buat cadangan data sistem untuk keamanan. Backup akan menyimpan semua data ke file JSON.
                                                </p>
                                                <div style="display: flex; gap: 10px;">
                                                    <button class="btn btn-primary" id="btnBackupSekarang">
                                                        <i class="fas fa-download"></i> Backup Sekarang
                                                    </button>
                                                    <button class="btn btn-secondary" id="btnRestoreBackup">
                                                        <i class="fas fa-upload"></i> Restore Backup
                                                    </button>
                                                    <input type="file" id="restoreFile" accept=".json" style="display: none;">
                                                </div>
                                                <div id="backupInfo" style="margin-top: 10px; font-size: 12px; color: var(--text-secondary);">
                                                    <p><i class="fas fa-info-circle"></i> Backup terakhir: <span id="lastBackupTime">-</span></p>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Reset Sistem</label>
                                                <p style="color: var(--text-secondary); margin-bottom: 15px;">
                                                    <strong>PERINGATAN:</strong> Aksi ini akan menghapus semua data dan mengembalikan ke pengaturan awal. Pastikan Anda sudah melakukan backup terlebih dahulu.
                                                </p>
                                                <button class="btn btn-danger" id="btnResetSistem">
                                                    <i class="fas fa-exclamation-triangle"></i> Reset Sistem ke Pengaturan Awal
                                                </button>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Informasi Sistem</label>
                                                <div class="system-info">
                                                    <p><strong>Versi:</strong> <span id="systemVersion">1.0.0</span></p>
                                                    <p><strong>Jumlah Data:</strong> <span id="totalSuratSistem">0</span> Surat, <span id="totalMahasiswaSistem">0</span> Mahasiswa</p>
                                                    <p><strong>Penyimpanan:</strong> <span id="storageInfo">MySQL Database</span></p>
                                                    <p><strong>Browser:</strong> <span id="browserInfo">-</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI KELUAR -->
    <div class="modal" id="logoutModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Konfirmasi Keluar</h3>
                <button class="header-btn" id="closeLogoutModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin keluar dari sistem?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelLogout">Batal</button>
                <button class="btn btn-primary" id="confirmLogout">Keluar</button>
            </div>
        </div>
    </div>

    <!-- MODAL BUAT/EDIT SURAT PERINGATAN -->
    <div class="modal modal-large" id="suratPeringatanModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalSuratTitle">Buat Surat Peringatan</h3>
                <button class="header-btn" id="closeSuratModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formSuratPeringatan" enctype="multipart/form-data">
                    <input type="hidden" id="surat_id" name="surat_id" value="">
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-info-circle"></i> Informasi Dasar
                        </div>
                        <div class="form-group">
                            <label for="nomorSurat">Nomor Surat *</label>
                            <input type="text" id="nomorSurat" name="nomor_surat" class="form-control" placeholder="SP/2023/001" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="mahasiswa">Pilih Mahasiswa *</label>
                            <select id="mahasiswa" name="mahasiswa_id" class="form-control" required>
                                <option value="">Pilih Mahasiswa</option>
                                <?php
                                $mahasiswa_query = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE status = 'aktif' ORDER BY nama");
                                while ($mhs = mysqli_fetch_assoc($mahasiswa_query)) {
                                    echo "<option value='{$mhs['id']}'>{$mhs['nama']} ({$mhs['nim']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="programStudi">Program Studi</label>
                                    <input type="text" id="programStudi" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="semester">Semester</label>
                                    <input type="text" id="semester" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-exclamation-triangle"></i> Detail Pelanggaran
                        </div>
                        <div class="form-group">
                            <label for="jenisPelanggaran">Jenis Pelanggaran *</label>
                            <select id="jenisPelanggaran" name="jenis_pelanggaran" class="form-control" required>
                                <option value="">Pilih Jenis Pelanggaran</option>
                                <option value="akademik">Pelanggaran Akademik</option>
                                <option value="etika">Pelanggaran Etika</option>
                                <option value="administrasi">Pelanggaran Administrasi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="keterangan">Keterangan Pelanggaran *</label>
                            <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Jelaskan secara detail tentang pelanggaran yang dilakukan" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="sanksi">Sanksi *</label>
                            <textarea id="sanksi" name="sanksi" class="form-control" placeholder="Jelaskan sanksi yang diberikan" rows="3" required></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-calendar-alt"></i> Informasi Tanggal
                        </div>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="tanggalSurat">Tanggal Surat *</label>
                                    <input type="date" id="tanggalSurat" name="tanggal_surat" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="tanggalPelanggaran">Tanggal Pelanggaran *</label>
                                    <input type="date" id="tanggalPelanggaran" name="tanggal_pelanggaran" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-paperclip"></i> Lampiran File
                        </div>
                        
                        <div class="form-group">
                            <label>Upload Bukti Pelanggaran (Opsional)</label>
                            <div class="file-upload-container">
                                <!-- Upload Gambar (JPG) -->
                                <div class="file-upload-button" style="margin-bottom: 10px;">
                                    <button type="button" class="btn btn-secondary" id="uploadJPG" style="width: 100%;">
                                        <i class="fas fa-image"></i> Upload Foto (JPG)
                                    </button>
                                    <input type="file" id="fileJPG" accept=".jpg,.jpeg" style="display: none;">
                                    <div class="file-info" id="jpgInfo" style="margin-top: 5px; font-size: 12px; color: var(--text-secondary);">
                                        <!-- Info file akan ditampilkan di sini -->
                                    </div>
                                </div>
                                
                                <!-- Upload Dokumen (PDF, DOC, XLS) -->
                                <div class="file-upload-button">
                                    <button type="button" class="btn btn-secondary" id="uploadDocument" style="width: 100%;">
                                        <i class="fas fa-file-alt"></i> Upload Dokumen (PDF/DOC/XLS)
                                    </button>
                                    <input type="file" id="fileDocument" accept=".pdf,.doc,.docx,.xls,.xlsx" style="display: none;">
                                    <div class="file-info" id="documentInfo" style="margin-top: 5px; font-size: 12px; color: var(--text-secondary);">
                                        <!-- Info file akan ditampilkan di sini -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preview file -->
                            <div class="file-preview-container" id="filePreviewContainer" style="margin-top: 15px; display: none;">
                                <h5 style="margin-bottom: 10px; font-size: 14px;">File Terupload:</h5>
                                <div class="preview-list" id="previewList">
                                    <!-- List file akan ditampilkan di sini -->
                                </div>
                            </div>
                            
                            <!-- Note -->
                            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                                <i class="fas fa-info-circle"></i> Maksimal ukuran file: 5MB per file. Format yang didukung:
                                <br>• Gambar: JPG, JPEG
                                <br>• Dokumen: PDF, DOC, DOCX, XLS, XLSX
                            </p>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-user-check"></i> Penandatangan & Status
                        </div>
                        <div class="form-group">
                            <label for="penandatangan">Penandatangan *</label>
                            <input type="text" id="penandatangan" name="penandatangan" class="form-control" placeholder="Nama penandatangan surat" required value="<?php echo htmlspecialchars($current_user['nama']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="statusSurat">Status Surat *</label>
                            <select id="statusSurat" name="status" class="form-control" required>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelSurat">Batal</button>
                <button class="btn btn-primary" id="btnPreviewSurat">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button class="btn btn-success" id="simpanSurat">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMPILAN SURAT -->
    <div class="modal modal-large" id="tampilanSuratModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Preview Surat Peringatan</h3>
                <button class="header-btn" id="closeTampilanSuratModal">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-success" id="btnDownloadSurat">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
            <div class="modal-body">
                <div id="suratContent">
                    <!-- Konten surat akan diisi oleh JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="tutupSurat">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL TEMPLATE SURAT -->
    <div class="modal" id="templateSuratModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pilih Template Surat</h3>
                <button class="header-btn" id="closeTemplateModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="template-grid">
                    <div class="template-card" data-template="template1">
                        <div class="template-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Template Akademik</h4>
                        <p>Surat peringatan untuk pelanggaran akademik</p>
                    </div>
                    
                    <div class="template-card" data-template="template2">
                        <div class="template-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h4>Template Etika</h4>
                        <p>Surat peringatan untuk pelanggaran etika</p>
                    </div>
                    
                    <div class="template-card" data-template="template3">
                        <div class="template-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h4>Template Administrasi</h4>
                        <p>Surat peringatan untuk kelalaian administrasi</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="closeTemplateModalBtn">Batal</button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH/EDIT MAHASISWA -->
    <div class="modal" id="tambahMahasiswaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="tambahMahasiswaModalTitle">Tambah Mahasiswa</h3>
                <button class="header-btn" id="closeTambahMahasiswaModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahMahasiswa">
                    <input type="hidden" id="mahasiswa_id" name="mahasiswa_id" value="">
                    <div class="form-group">
                        <label for="namaMahasiswa">Nama Lengkap *</label>
                        <input type="text" id="namaMahasiswa" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nimMahasiswa">NIM *</label>
                        <input type="text" id="nimMahasiswa" name="nim" class="form-control" placeholder="Masukkan NIM" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="prodiMahasiswa">Program Studi *</label>
                                <select id="prodiMahasiswa" name="program_studi" class="form-control" required>
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Elektro">Teknik Elektro</option>
                                    <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="semesterMahasiswa">Semester *</label>
                                <input type="number" id="semesterMahasiswa" name="semester" class="form-control" placeholder="Semester" min="1" max="14" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="usernameMahasiswa">Username *</label>
                        <input type="text" id="usernameMahasiswa" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="passwordMahasiswa">Password *</label>
                        <div class="password-container">
                            <input type="password" id="passwordMahasiswa" name="password" class="form-control" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" id="togglePasswordMahasiswa">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="roleMahasiswa">Peran *</label>
                        <select id="roleMahasiswa" name="role" class="form-control" required>
                            <option value="">Pilih Peran</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                      
                    <div class="form-group">
                        <label for="statusMahasiswa">Status *</label>
                        <select id="statusMahasiswa" name="status" class="form-control" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelTambahMahasiswa">Batal</button>
                <button class="btn btn-primary" id="simpanMahasiswa">Simpan</button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="confirmModalTitle">Konfirmasi</h3>
                <button class="header-btn" id="closeConfirmModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Apakah Anda yakin?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelConfirm">Batal</button>
                <button class="btn btn-danger" id="confirmAction">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
    <script>
        // Global variables
        let editingSuratId = null;
        let editingMahasiswaId = null;
        let editingAkunId = null;
        let currentSuratView = null;
        let uploadedFiles = {
            images: [],
            documents: []
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard Admin Loaded');
            
            // Initialize
            initializeDashboard();
            setupEventListeners();
            updateClock();
            setInterval(updateClock, 1000);
            
            // Load initial data
            loadPengaturanData();
        });
        
        function initializeDashboard() {
            // Load saved theme
            const savedTheme = localStorage.getItem('dashboardTheme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                document.getElementById('themeToggle').innerHTML = '<i class="fas fa-sun"></i>';
            }
            
            // Setup tabs
            setupTabs();
        }
        
        function setupEventListeners() {
            // Menu toggle
            document.getElementById('menuToggle').addEventListener('click', toggleSidebar);
            document.getElementById('sidebarOverlay').addEventListener('click', toggleSidebar);
            
            // Theme toggle
            document.getElementById('themeToggle').addEventListener('click', toggleTheme);
            
            // Navigation
            document.getElementById('navDashboard').addEventListener('click', () => showPage('dashboardMain', 'Dashboard', 'navDashboard'));
            document.getElementById('navRiwayat').addEventListener('click', () => {
                showPage('riwayatPage', 'Riwayat Surat', 'navRiwayat');
                loadRiwayat();
            });
            document.getElementById('navArsipLink').addEventListener('click', () => {
                showPage('arsipPage', 'Arsip Surat', 'navArsipLink');
                loadArsip();
            });
            document.getElementById('navDataMahasiswaLink').addEventListener('click', () => {
                showPage('dataMahasiswaPage', 'Data Mahasiswa', 'navDataMahasiswaLink');
                loadMahasiswa();
            });
            document.getElementById('navPengaturan').addEventListener('click', () => {
                showPage('pengaturanPage', 'Pengaturan Sistem', 'navPengaturan');
                loadAkunTable();
            });
            
            // Quick actions
            document.getElementById('btnBuatSurat').addEventListener('click', () => showSuratModal());
            document.getElementById('btnTemplateSurat').addEventListener('click', () => showModal('templateSuratModal'));
            document.getElementById('btnLihatSurat').addEventListener('click', () => showPage('riwayatPage', 'Riwayat Surat', 'navRiwayat'));
            document.getElementById('btnRiwayat').addEventListener('click', () => showPage('riwayatPage', 'Riwayat Surat', 'navRiwayat'));
            
            // Tambah data
            document.getElementById('btnTambahMahasiswa').addEventListener('click', () => showTambahMahasiswaModal());
            document.getElementById('btnTambahSuratRiwayat').addEventListener('click', () => showSuratModal());
            
            // Back buttons
            document.getElementById('kembaliDariRiwayat').addEventListener('click', () => showPage('dashboardMain', 'Dashboard', 'navDashboard'));
            document.getElementById('kembaliDariArsip').addEventListener('click', () => showPage('dashboardMain', 'Dashboard', 'navDashboard'));
            document.getElementById('kembaliDariMahasiswa').addEventListener('click', () => showPage('dashboardMain', 'Dashboard', 'navDashboard'));
            document.getElementById('kembaliDariPengaturan').addEventListener('click', () => showPage('dashboardMain', 'Dashboard', 'navDashboard'));
            
            // Search and filter
            document.getElementById('btnCariRiwayat').addEventListener('click', filterRiwayat);
            document.getElementById('btnResetRiwayat').addEventListener('click', resetFilterRiwayat);
            document.getElementById('btnCariArsip').addEventListener('click', filterArsip);
            document.getElementById('btnResetArsip').addEventListener('click', resetFilterArsip);
            document.getElementById('btnCariMahasiswa').addEventListener('click', filterMahasiswa);
            document.getElementById('btnResetMahasiswa').addEventListener('click', resetFilterMahasiswa);
            
            // Refresh
            document.getElementById('btnRefreshSurat').addEventListener('click', refreshDashboard);
            
            // Mahasiswa dropdown change
            document.getElementById('mahasiswa').addEventListener('change', updateMahasiswaInfo);
            
            // Simpan data
            document.getElementById('simpanSurat').addEventListener('click', simpanSurat);
            document.getElementById('simpanMahasiswa').addEventListener('click', simpanMahasiswa);
            
            // Preview surat
            document.getElementById('btnPreviewSurat').addEventListener('click', previewSurat);
            document.getElementById('btnDownloadSurat').addEventListener('click', downloadSuratPDF);
            
            // Pengaturan
            document.getElementById('simpanPengaturanUmum').addEventListener('click', simpanPengaturanUmum);
            document.getElementById('btnSimpanAkun').addEventListener('click', simpanAkun);
            document.getElementById('btnResetFormAkun').addEventListener('click', resetFormAkun);
            document.getElementById('btnBackupSekarang').addEventListener('click', backupSekarang);
            document.getElementById('btnRestoreBackup').addEventListener('click', () => document.getElementById('restoreFile').click());
            document.getElementById('restoreFile').addEventListener('change', handleRestoreFile);
            document.getElementById('btnResetSistem').addEventListener('click', resetSistem);
            document.getElementById('togglePasswordAkun').addEventListener('click', togglePasswordAkun);
            document.getElementById('togglePasswordMahasiswa').addEventListener('click', togglePasswordMahasiswa);
            
            // Logout
            document.getElementById('logoutBtn').addEventListener('click', () => showModal('logoutModal'));
            document.getElementById('confirmLogout').addEventListener('click', handleLogout);
            
            // Template selection
            document.querySelectorAll('.template-card').forEach(card => {
                card.addEventListener('click', function() {
                    const template = this.getAttribute('data-template');
                    applyTemplate(template);
                    hideModal('templateSuratModal');
                });
            });
            
            // Modal close buttons
            setupModalCloseListeners();
            
            // File upload
            setupFileUploadListeners();
        }
        
        function setupModalCloseListeners() {
            const modals = {
                'logoutModal': ['closeLogoutModal', 'cancelLogout'],
                'suratPeringatanModal': ['closeSuratModal', 'cancelSurat'],
                'tampilanSuratModal': ['closeTampilanSuratModal', 'tutupSurat'],
                'templateSuratModal': ['closeTemplateModal', 'closeTemplateModalBtn'],
                'tambahMahasiswaModal': ['closeTambahMahasiswaModal', 'cancelTambahMahasiswa'],
                'confirmModal': ['closeConfirmModal', 'cancelConfirm']
            };
            
            Object.keys(modals).forEach(modalId => {
                modals[modalId].forEach(buttonId => {
                    const button = document.getElementById(buttonId);
                    if (button) {
                        button.addEventListener('click', () => hideModal(modalId));
                    }
                });
            });
        }
        
        function setupTabs() {
            document.querySelectorAll('.tab-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    const tabContent = document.getElementById(tabId);
                    if (tabContent) {
                        tabContent.classList.add('active');
                    }
                });
            });
        }
        
        function setupFileUploadListeners() {
            // Upload JPG
            document.getElementById('uploadJPG').addEventListener('click', () => {
                document.getElementById('fileJPG').click();
            });
            
            document.getElementById('fileJPG').addEventListener('change', function(e) {
                handleFileUpload(e, 'image');
            });
            
            // Upload Document
            document.getElementById('uploadDocument').addEventListener('click', () => {
                document.getElementById('fileDocument').click();
            });
            
            document.getElementById('fileDocument').addEventListener('change', function(e) {
                handleFileUpload(e, 'document');
            });
        }
        
        function handleFileUpload(event, type) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Check file size (max 5MB)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showNotification(`File ${file.name} terlalu besar! Maksimal 5MB`, 'error');
                event.target.value = '';
                return;
            }
            
            // Validate file type
            let isValidType = false;
            if (type === 'image') {
                const validImageTypes = ['image/jpeg', 'image/jpg'];
                isValidType = validImageTypes.includes(file.type.toLowerCase());
                if (!isValidType) {
                    showNotification('Format file gambar harus JPG/JPEG', 'error');
                    event.target.value = '';
                    return;
                }
            } else if (type === 'document') {
                const validExtensions = ['.pdf', '.doc', '.docx', '.xls', '.xlsx'];
                const fileName = file.name.toLowerCase();
                isValidType = validExtensions.some(ext => fileName.endsWith(ext));
                
                if (!isValidType) {
                    showNotification('Format file harus PDF, DOC, DOCX, XLS, atau XLSX', 'error');
                    event.target.value = '';
                    return;
                }
            }
            
            // Add to uploaded files
            const fileData = {
                name: file.name,
                size: file.size,
                type: type,
                fileObject: file
            };
            
            if (type === 'image') {
                uploadedFiles.images = [fileData];
                document.getElementById('jpgInfo').innerHTML = `
                    <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i>
                    ${file.name} (${formatFileSize(file.size)})
                `;
            } else if (type === 'document') {
                uploadedFiles.documents = [fileData];
                document.getElementById('documentInfo').innerHTML = `
                    <i class="fas fa-check-circle" style="color: var(--success); margin-right: 5px;"></i>
                    ${file.name} (${formatFileSize(file.size)})
                `;
            }
            
            showNotification(`File ${file.name} berhasil diupload`, 'success');
            updateFilePreview();
        }
        
        function updateFilePreview() {
            const container = document.getElementById('filePreviewContainer');
            const list = document.getElementById('previewList');
            
            const allFiles = [...uploadedFiles.images, ...uploadedFiles.documents];
            
            if (allFiles.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            list.innerHTML = '';
            
            allFiles.forEach((file, index) => {
                const fileType = file.type === 'image' ? 'Gambar' : 'Dokumen';
                const fileIcon = file.type === 'image' ? 'fa-image' : 'fa-file-alt';
                
                const item = document.createElement('div');
                item.className = 'preview-item';
                item.innerHTML = `
                    <div class="preview-info">
                        <div class="preview-icon">
                            <i class="fas ${fileIcon}"></i>
                        </div>
                        <div class="preview-details">
                            <h6>${file.name}</h6>
                            <p>${fileType} • ${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button class="preview-action-btn" onclick="removeFile(${index}, '${file.type}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                list.appendChild(item);
            });
        }
        
        function removeFile(index, type) {
            if (type === 'image') {
                uploadedFiles.images.splice(index, 1);
                document.getElementById('jpgInfo').innerHTML = '';
                document.getElementById('fileJPG').value = '';
            } else if (type === 'document') {
                uploadedFiles.documents.splice(index, 1);
                document.getElementById('documentInfo').innerHTML = '';
                document.getElementById('fileDocument').value = '';
            }
            
            showNotification('File berhasil dihapus', 'success');
            updateFilePreview();
        }
        
        // ==================== FUNGSI UTAMA ====================
        
        function showPage(pageId, breadcrumbText, navId) {
            // Hide all pages
            ['dashboardMain', 'riwayatPage', 'arsipPage', 'dataMahasiswaPage', 'pengaturanPage'].forEach(id => {
                document.getElementById(id).style.display = 'none';
            });
            
            // Show selected page
            document.getElementById(pageId).style.display = 'block';
            document.getElementById('breadcrumb').textContent = breadcrumbText;
            
            // Update active nav
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            document.getElementById(navId).classList.add('active');
        }
        
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
        
        function toggleTheme() {
            const themeToggle = document.getElementById('themeToggle');
            if (document.body.classList.contains('dark-theme')) {
                document.body.classList.remove('dark-theme');
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('dashboardTheme', 'light');
            } else {
                document.body.classList.add('dark-theme');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('dashboardTheme', 'dark');
            }
        }
        
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID');
        }
        
        // ==================== FUNGSI SURAT ====================
        
        function showSuratModal(suratId = null) {
            editingSuratId = suratId;
            const title = document.getElementById('modalSuratTitle');
            
            // Reset form
            document.getElementById('formSuratPeringatan').reset();
            uploadedFiles = { images: [], documents: [] };
            updateFilePreview();
            document.getElementById('jpgInfo').innerHTML = '';
            document.getElementById('documentInfo').innerHTML = '';
            
            if (suratId) {
                title.textContent = 'Edit Surat Peringatan';
                loadSuratData(suratId);
            } else {
                title.textContent = 'Buat Surat Peringatan';
                
                // Generate nomor surat
                generateNomorSurat();
                
                // Set tanggal default
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('tanggalSurat').value = today;
                document.getElementById('tanggalPelanggaran').value = today;
            }
            
            showModal('suratPeringatanModal');
        }
        
        function generateNomorSurat() {
            const currentYear = new Date().getFullYear();
            fetch(`ajax/generate_nomor_surat.php?year=${currentYear}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('nomorSurat').value = `SP/${currentYear}/${data.nomor}`;
                    }
                });
        }
        
        function loadSuratData(suratId) {
            fetch(`ajax/get_surat.php?id=${suratId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('surat_id').value = data.id;
                        document.getElementById('nomorSurat').value = data.nomor_surat;
                        document.getElementById('mahasiswa').value = data.mahasiswa_id;
                        document.getElementById('jenisPelanggaran').value = data.jenis_pelanggaran;
                        document.getElementById('keterangan').value = data.keterangan;
                        document.getElementById('sanksi').value = data.sanksi;
                        document.getElementById('tanggalSurat').value = data.tanggal_surat;
                        document.getElementById('tanggalPelanggaran').value = data.tanggal_pelanggaran;
                        document.getElementById('penandatangan').value = data.penandatangan;
                        document.getElementById('statusSurat').value = data.status;
                        
                        // Update mahasiswa info
                        updateMahasiswaInfo();
                    }
                });
        }
        
        function updateMahasiswaInfo() {
            const mahasiswaId = document.getElementById('mahasiswa').value;
            if (!mahasiswaId) return;
            
            fetch(`ajax/get_mahasiswa.php?id=${mahasiswaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('programStudi').value = data.program_studi;
                        document.getElementById('semester').value = data.semester;
                    }
                });
        }
        
        function simpanSurat() {
            const form = document.getElementById('formSuratPeringatan');
            const formData = new FormData(form);
            
            // Append uploaded files
            if (uploadedFiles.images.length > 0) {
                formData.append('image_file', uploadedFiles.images[0].fileObject);
            }
            if (uploadedFiles.documents.length > 0) {
                formData.append('document_file', uploadedFiles.documents[0].fileObject);
            }
            
            const endpoint = editingSuratId ? 'ajax/update_surat.php' : 'ajax/tambah_surat.php';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    hideModal('suratPeringatanModal');
                    refreshDashboard();
                } else {
                    showNotification(data.message, 'error');
                }
            });
        }
        
        function previewSurat() {
            // Simpan data sementara untuk preview
            currentSuratView = {
                nomor_surat: document.getElementById('nomorSurat').value,
                mahasiswa_id: document.getElementById('mahasiswa').value,
                jenis_pelanggaran: document.getElementById('jenisPelanggaran').value,
                keterangan: document.getElementById('keterangan').value,
                sanksi: document.getElementById('sanksi').value,
                tanggal_surat: document.getElementById('tanggalSurat').value,
                tanggal_pelanggaran: document.getElementById('tanggalPelanggaran').value,
                penandatangan: document.getElementById('penandatangan').value,
                status: document.getElementById('statusSurat').value
            };
            
            // Tampilkan preview
            fetch('ajax/preview_surat.php', {
                method: 'POST',
                body: new URLSearchParams(currentSuratView)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const suratContent = document.getElementById('suratContent');
                    suratContent.innerHTML = generateSuratHTML(data.data);
                    hideModal('suratPeringatanModal');
                    showModal('tampilanSuratModal');
                }
            });
        }
        
        function generateSuratHTML(data) {
            const jenisText = {
                'akademik': 'Pelanggaran Akademik',
                'etika': 'Pelanggaran Etika',
                'administrasi': 'Pelanggaran Administrasi',
                'lainnya': 'Pelanggaran Lainnya'
            }[data.jenis_pelanggaran] || 'Pelanggaran';
            
            const statusText = {
                'approved': 'Disetujui',
                'rejected': 'Ditolak',
                'pending': 'Menunggu'
            }[data.status] || 'Menunggu';
            
            return `
                <div class="surat-container" id="suratUntukPDF">
                    <div class="surat-header">
                        <h1>SURAT PERINGATAN</h1>
                        <h2>Sistem Surat Peringatan Polibatam</h2>
                        <p>Politeknik Negeri Batam</p>
                    </div>
                    
                    <div class="surat-content">
                        <div class="surat-field">
                            <label>Nomor:</label>
                            <span>${data.nomor_surat}</span>
                        </div>
                        <div class="surat-field">
                            <label>Status:</label>
                            <span>${statusText}</span>
                        </div>
                        <div class="surat-field">
                            <label>Tanggal:</label>
                            <span>${formatTanggalIndonesia(data.tanggal_surat)}</span>
                        </div>
                        
                        <div class="surat-paragraph">
                            <p>Yang bertanda tangan di bawah ini:</p>
                            <p><strong>${data.penandatangan}</strong></p>
                            <p>Dengan ini memberikan Surat Peringatan kepada:</p>
                        </div>
                        
                        <div class="surat-field">
                            <label>Nama:</label>
                            <span>${data.mahasiswa_nama}</span>
                        </div>
                        <div class="surat-field">
                            <label>NIM:</label>
                            <span>${data.nim}</span>
                        </div>
                        <div class="surat-field">
                            <label>Program Studi:</label>
                            <span>${data.program_studi}</span>
                        </div>
                        <div class="surat-field">
                            <label>Semester:</label>
                            <span>${data.semester}</span>
                        </div>
                        
                        <div class="surat-paragraph">
                            <p><strong>Jenis Pelanggaran:</strong> ${jenisText}</p>
                            <p><strong>Keterangan:</strong></p>
                            <p>${data.keterangan.replace(/\n/g, '<br>')}</p>
                            <p><strong>Sanksi:</strong></p>
                            <p>${data.sanksi.replace(/\n/g, '<br>')}</p>
                        </div>
                        
                        <div class="surat-paragraph">
                            <p>Surat peringatan ini diberikan atas pelanggaran yang terjadi pada tanggal: <strong>${formatTanggalIndonesia(data.tanggal_pelanggaran)}</strong></p>
                        </div>
                    </div>
                    
                    <div class="surat-footer">
                        <div class="ttd-container">
                            <div class="ttd-space"></div>
                            <p><strong>${data.penandatangan}</strong></p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function tampilkanSurat(id) {
            fetch(`ajax/get_surat.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentSuratView = data;
                        const suratContent = document.getElementById('suratContent');
                        suratContent.innerHTML = generateSuratHTML(data);
                        showModal('tampilanSuratModal');
                    }
                });
        }
        
        function editSurat(id) {
            showSuratModal(id);
        }
        
        function hapusSurat(id) {
            document.getElementById('confirmModalTitle').textContent = 'Hapus Surat Peringatan';
            document.getElementById('confirmModalMessage').textContent = 'Apakah Anda yakin ingin menghapus surat ini?';
            
            document.getElementById('confirmAction').onclick = function() {
                fetch(`ajax/hapus_surat.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            hideModal('confirmModal');
                            refreshDashboard();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
            };
            
            showModal('confirmModal');
        }
        
        function downloadSuratPDF() {
            if (!currentSuratView || !currentSuratView.id) {
                showNotification('Tidak ada surat untuk di-download!', 'error');
                return;
            }
            
            window.open(`ajax/generate_pdf.php?id=${currentSuratView.id}`, '_blank');
        }
        
        // ==================== FUNGSI MAHASISWA ====================
        
        function showTambahMahasiswaModal(mahasiswaId = null) {
            editingMahasiswaId = mahasiswaId;
            const title = document.getElementById('tambahMahasiswaModalTitle');
            
            if (mahasiswaId) {
                title.textContent = 'Edit Mahasiswa';
                loadMahasiswaData(mahasiswaId);
            } else {
                title.textContent = 'Tambah Mahasiswa';
                document.getElementById('formTambahMahasiswa').reset();
                document.getElementById('roleMahasiswa').value = 'mahasiswa';
                document.getElementById('statusMahasiswa').value = 'aktif';
            }
            
            showModal('tambahMahasiswaModal');
        }
        
        function loadMahasiswaData(mahasiswaId) {
            fetch(`ajax/get_mahasiswa.php?id=${mahasiswaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('mahasiswa_id').value = data.id;
                        document.getElementById('namaMahasiswa').value = data.nama;
                        document.getElementById('nimMahasiswa').value = data.nim;
                        document.getElementById('prodiMahasiswa').value = data.program_studi;
                        document.getElementById('semesterMahasiswa').value = data.semester;
                        document.getElementById('usernameMahasiswa').value = data.username;
                        document.getElementById('passwordMahasiswa').value = data.password;
                        document.getElementById('statusMahasiswa').value = data.status;
                    }
                });
        }
        
        function simpanMahasiswa() {
            const form = document.getElementById('formTambahMahasiswa');
            const formData = new FormData(form);
            
            const endpoint = editingMahasiswaId ? 'ajax/update_mahasiswa.php' : 'ajax/tambah_mahasiswa.php';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    hideModal('tambahMahasiswaModal');
                    loadMahasiswa();
                    refreshDropdownMahasiswa();
                } else {
                    showNotification(data.message, 'error');
                }
            });
        }
        
        function editMahasiswa(id) {
            showTambahMahasiswaModal(id);
        }
        
        function hapusMahasiswa(id) {
            document.getElementById('confirmModalTitle').textContent = 'Hapus Mahasiswa';
            document.getElementById('confirmModalMessage').textContent = 'Apakah Anda yakin ingin menghapus mahasiswa ini?';
            
            document.getElementById('confirmAction').onclick = function() {
                fetch(`ajax/hapus_mahasiswa.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            hideModal('confirmModal');
                            loadMahasiswa();
                            refreshDropdownMahasiswa();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
            };
            
            showModal('confirmModal');
        }
        
        // ==================== FUNGSI LOAD DATA ====================
        
        function loadRiwayat() {
            document.getElementById('riwayatTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch('ajax/load_riwayat.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('riwayatTableContainer').innerHTML = html;
                });
        }
        
        function loadArsip() {
            document.getElementById('arsipTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch('ajax/load_arsip.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('arsipTableContainer').innerHTML = html;
                });
        }
        
        function loadMahasiswa() {
            document.getElementById('mahasiswaTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch('ajax/load_mahasiswa.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('mahasiswaTableContainer').innerHTML = html;
                });
        }
        
        function loadAkunTable() {
            document.getElementById('akunTableBody').innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner"></i> Memuat data...</td></tr>';
            
            fetch('ajax/load_akun.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('akunTableBody').innerHTML = html;
                });
        }
        
        function loadPengaturanData() {
            fetch('ajax/load_pengaturan.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('namaAplikasi').value = data.nama_aplikasi;
                        document.getElementById('versiAplikasi').value = data.versi_aplikasi;
                        document.getElementById('namaInstitusi').value = data.nama_institusi;
                        document.getElementById('alamatInstitusi').value = data.alamat_institusi;
                        document.getElementById('telpInstitusi').value = data.telp_institusi;
                        document.getElementById('emailInstitusi').value = data.email_institusi;
                        document.getElementById('lastBackupTime').textContent = data.backup_terakhir || '-';
                        document.getElementById('systemVersion').textContent = data.versi_aplikasi;
                        document.getElementById('totalSuratSistem').textContent = data.total_surat;
                        document.getElementById('totalMahasiswaSistem').textContent = data.total_mahasiswa;
                        document.getElementById('browserInfo').textContent = navigator.userAgent;
                    }
                });
        }
        
        // ==================== FUNGSI FILTER ====================
        
        function filterRiwayat() {
            const keyword = document.getElementById('cariNamaRiwayat').value;
            const jenis = document.getElementById('filterJenisRiwayat').value;
            const status = document.getElementById('filterStatusRiwayat').value;
            
            document.getElementById('riwayatTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch(`ajax/filter_riwayat.php?keyword=${keyword}&jenis=${jenis}&status=${status}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('riwayatTableContainer').innerHTML = html;
                });
        }
        
        function resetFilterRiwayat() {
            document.getElementById('cariNamaRiwayat').value = '';
            document.getElementById('filterJenisRiwayat').value = '';
            document.getElementById('filterStatusRiwayat').value = '';
            filterRiwayat();
        }
        
        function filterArsip() {
            const nama = document.getElementById('cariNamaArsip').value;
            const nim = document.getElementById('cariNIMArsip').value;
            const jenis = document.getElementById('filterJenisArsip').value;
            
            document.getElementById('arsipTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch(`ajax/filter_arsip.php?nama=${nama}&nim=${nim}&jenis=${jenis}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('arsipTableContainer').innerHTML = html;
                });
        }
        
        function resetFilterArsip() {
            document.getElementById('cariNamaArsip').value = '';
            document.getElementById('cariNIMArsip').value = '';
            document.getElementById('filterJenisArsip').value = '';
            filterArsip();
        }
        
        function filterMahasiswa() {
            const nama = document.getElementById('cariNamaMahasiswa').value;
            const nim = document.getElementById('cariNIMMahasiswa').value;
            const prodi = document.getElementById('filterProdiMahasiswa').value;
            
            document.getElementById('mahasiswaTableContainer').innerHTML = '<div class="loading"><i class="fas fa-spinner"></i> Memuat data...</div>';
            
            fetch(`ajax/filter_mahasiswa.php?nama=${nama}&nim=${nim}&prodi=${prodi}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('mahasiswaTableContainer').innerHTML = html;
                });
        }
        
        function resetFilterMahasiswa() {
            document.getElementById('cariNamaMahasiswa').value = '';
            document.getElementById('cariNIMMahasiswa').value = '';
            document.getElementById('filterProdiMahasiswa').value = '';
            filterMahasiswa();
        }
        
        // ==================== FUNGSI PENGATURAN ====================
        
        function simpanPengaturanUmum() {
            const data = {
                nama_aplikasi: document.getElementById('namaAplikasi').value,
                versi_aplikasi: document.getElementById('versiAplikasi').value,
                nama_institusi: document.getElementById('namaInstitusi').value,
                alamat_institusi: document.getElementById('alamatInstitusi').value,
                telp_institusi: document.getElementById('telpInstitusi').value,
                email_institusi: document.getElementById('emailInstitusi').value
            };
            
            fetch('ajax/simpan_pengaturan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Pengaturan berhasil disimpan!', 'success');
                    loadPengaturanData();
                } else {
                    showNotification('Gagal menyimpan pengaturan', 'error');
                }
            });
        }
        
        function simpanAkun() {
            const formData = new FormData(document.getElementById('formTambahAkun'));
            
            const endpoint = editingAkunId ? 'ajax/update_akun.php' : 'ajax/simpan_akun.php';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    resetFormAkun();
                    loadAkunTable();
                } else {
                    showNotification(data.message, 'error');
                }
            });
        }
        
        function resetFormAkun() {
            document.getElementById('formTambahAkun').reset();
            document.getElementById('akun_id').value = '';
            editingAkunId = null;
        }
        
        function editAkun(id) {
            editingAkunId = id;
            
            fetch(`ajax/get_akun.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('akun_id').value = data.id;
                        document.getElementById('usernameAkun').value = data.username;
                        document.getElementById('namaAkun').value = data.nama;
                        document.getElementById('passwordAkun').value = data.password;
                        document.getElementById('roleAkun').value = data.role;
                        document.getElementById('statusAkun').value = data.status;
                        document.getElementById('nimAkun').value = data.nim || '';
                    }
                });
        }
        
        function hapusAkun(id) {
            if (confirm('Apakah Anda yakin ingin menghapus akun ini?')) {
                fetch(`ajax/hapus_akun.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            loadAkunTable();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
            }
        }
        
        function backupSekarang() {
            showNotification('Membuat backup...', 'info');
            
            fetch('ajax/backup_database.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Download backup file
                        const link = document.createElement('a');
                        link.href = 'data:application/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(data.data, null, 2));
                        link.download = `backup-${new Date().toISOString().slice(0,10)}.json`;
                        link.click();
                        
                        showNotification('Backup berhasil dibuat!', 'success');
                        loadPengaturanData();
                    } else {
                        showNotification('Gagal membuat backup', 'error');
                    }
                });
        }
        
        function handleRestoreFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (!confirm('PERINGATAN: Restore akan mengganti semua data dengan data backup. Lanjutkan?')) {
                event.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const backupData = JSON.parse(e.target.result);
                
                fetch('ajax/restore_database.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(backupData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Backup berhasil direstore!', 'success');
                        location.reload();
                    } else {
                        showNotification('Gagal restore backup', 'error');
                    }
                    event.target.value = '';
                });
            };
            reader.readAsText(file);
        }
        
        function resetSistem() {
            if (confirm('PERINGATAN: Reset sistem akan menghapus SEMUA DATA kecuali akun admin. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')) {
                if (confirm('Apakah Anda BENAR-BENAR yakin?')) {
                    fetch('ajax/reset_database.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Sistem berhasil direset!', 'success');
                                location.reload();
                            } else {
                                showNotification('Gagal reset sistem', 'error');
                            }
                        });
                }
            }
        }
        
        function togglePasswordAkun() {
            const passwordInput = document.getElementById('passwordAkun');
            const icon = document.getElementById('togglePasswordAkun').querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        function togglePasswordMahasiswa() {
            const passwordInput = document.getElementById('passwordMahasiswa');
            const icon = document.getElementById('togglePasswordMahasiswa').querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // ==================== FUNGSI TAMBAHAN ====================
        
        function applyTemplate(templateType) {
            let keterangan = '';
            let sanksi = '';
            
            switch(templateType) {
                case 'template1':
                    keterangan = 'Mahasiswa tidak mengumpulkan tugas mata kuliah minimal 3 kali berturut-turut atau memiliki nilai di bawah standar yang ditetapkan.';
                    sanksi = 'Peringatan tertulis pertama, wajib konsultasi dengan dosen pengampu, dan mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari kerja.';
                    document.getElementById('jenisPelanggaran').value = 'akademik';
                    break;
                case 'template2':
                    keterangan = 'Mahasiswa melakukan pelanggaran etika seperti terlambat masuk kelas tanpa izin, tidak menghormati dosen atau teman sekelas, atau melakukan tindakan tidak terpuji lainnya.';
                    sanksi = 'Peringatan tertulis, wajib membuat surat pernyataan, dan mengikuti sesi konseling dengan bagian kemahasiswaan.';
                    document.getElementById('jenisPelanggaran').value = 'etika';
                    break;
                case 'template3':
                    keterangan = 'Mahasiswa tidak memenuhi kewajiban administratif seperti keterlambatan pembayaran SPP, tidak melengkapi dokumen administrasi, atau tidak mengikuti prosedur yang ditetapkan.';
                    sanksi = 'Peringatan tertulis, dikenakan denda sesuai ketentuan, dan wajib melengkapi administrasi dalam waktu 3 hari kerja.';
                    document.getElementById('jenisPelanggaran').value = 'administrasi';
                    break;
            }
            
            document.getElementById('keterangan').value = keterangan;
            document.getElementById('sanksi').value = sanksi;
            
            showNotification('Template berhasil diterapkan', 'success');
        }
        
        function refreshDashboard() {
            location.reload();
        }
        
        function refreshDropdownMahasiswa() {
            // Function to refresh dropdown after adding new student
            fetch('ajax/get_mahasiswa_dropdown.php')
                .then(response => response.text())
                .then(html => {
                    const select = document.getElementById('mahasiswa');
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Pilih Mahasiswa</option>' + html;
                    select.value = currentValue;
                });
        }
        
        function handleLogout() {
            fetch('ajax/logout.php')
                .then(() => {
                    window.location.href = 'login.php';
                });
        }
        
        // ==================== HELPER FUNCTIONS ====================
        
        function formatTanggalIndonesia(dateString) {
            const date = new Date(dateString);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function showNotification(message, type = 'info') {
            // Remove existing notification
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            // Add styles
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--primary)'};
                color: white;
                padding: 15px 20px;
                border-radius: var(--border-radius);
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                max-width: 400px;
                z-index: 9999;
                box-shadow: var(--shadow);
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            // Add animation styles if not exists
            if (!document.querySelector('#notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                    .notification-content {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .notification-close {
                        background: none;
                        border: none;
                        color: white;
                        cursor: pointer;
                        font-size: 0.9rem;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
            
            // Close button
            notification.querySelector('.notification-close').addEventListener('click', () => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            });
        }
        
        // Expose functions to global scope for onclick attributes
        window.tampilkanSurat = tampilkanSurat;
        window.editSurat = editSurat;
        window.hapusSurat = hapusSurat;
        window.editMahasiswa = editMahasiswa;
        window.hapusMahasiswa = hapusMahasiswa;
        window.editAkun = editAkun;
        window.hapusAkun = hapusAkun;
        window.removeFile = removeFile;
    </script>
</body>
</html>
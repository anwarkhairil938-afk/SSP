<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek role mahasiswa
if ($_SESSION['role'] !== 'mahasiswa') {
    header("Location: unauthorized.php");
    exit();
}

// Include koneksi database
require_once "auth_koneksi/koneksi.php";

// Ambil data user (mahasiswa)
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM mahasiswa WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$current_mahasiswa = mysqli_fetch_assoc($result_user);

// Hitung statistik untuk mahasiswa ini
$total_surat = mysqli_fetch_assoc(mysqli_query($koneksi, 
    "SELECT COUNT(*) as total FROM surat_peringatan WHERE mahasiswa_id = '$user_id'"))['total'];
    
$pending_surat = mysqli_fetch_assoc(mysqli_query($koneksi, 
    "SELECT COUNT(*) as total FROM surat_peringatan WHERE mahasiswa_id = '$user_id' AND status = 'pending'"))['total'];
    
$surat_disetujui = mysqli_fetch_assoc(mysqli_query($koneksi, 
    "SELECT COUNT(*) as total FROM surat_peringatan WHERE mahasiswa_id = '$user_id' AND status = 'approved'"))['total'];
    
// HITUNG NOTIFIKASI: surat yang disetujui dalam 7 hari terakhir
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$notifikasi_query = mysqli_query($koneksi, 
    "SELECT COUNT(*) as total FROM surat_peringatan 
     WHERE mahasiswa_id = '$user_id' 
     AND status = 'approved' 
     AND DATE(updated_at) >= '$seven_days_ago'");
$notifikasi_count = mysqli_fetch_assoc($notifikasi_query)['total'];

// Ambil data surat terbaru untuk dashboard
$query_recent = "SELECT s.* 
                 FROM surat_peringatan s 
                 WHERE s.mahasiswa_id = '$user_id' 
                 ORDER BY s.created_at DESC LIMIT 5";
$result_recent = mysqli_query($koneksi, $query_recent);
$recent_surat = [];
while ($row = mysqli_fetch_assoc($result_recent)) {
    $recent_surat[] = $row;
}

// Ambil data notifikasi terbaru
$query_notif = "SELECT s.* 
                FROM surat_peringatan s 
                WHERE s.mahasiswa_id = '$user_id' 
                AND s.status = 'approved'
                AND DATE(s.updated_at) >= '$seven_days_ago'
                ORDER BY s.updated_at DESC LIMIT 5";
$result_notif = mysqli_query($koneksi, $query_notif);
$notifikasi_data = [];
while ($row = mysqli_fetch_assoc($result_notif)) {
    $notifikasi_data[] = $row;
}

// Ambil semua data surat untuk riwayat (jika diperlukan)
$query_all = "SELECT s.* 
              FROM surat_peringatan s 
              WHERE s.mahasiswa_id = '$user_id' 
              ORDER BY s.created_at DESC";
$result_all = mysqli_query($koneksi, $query_all);
$all_surat = [];
while ($row = mysqli_fetch_assoc($result_all)) {
    $all_surat[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sistem Surat Peringatan Polibatam</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4a148c;
            --secondary-color: #7b1fa2;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            
            .overlay.show {
                display: block;
            }
        }
        
        .stat-card {
            border-radius: 12px;
            color: white;
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 8px;
        }
        
        .recent-item:hover {
            background-color: #f8f9fa;
        }
        
        /* Animasi loading */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        
        .spinner-overlay.show {
            display: flex;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .new-badge {
            background-color: #dc3545 !important;
        }
        
        .card-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .nav-link {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 5px;
            transition: all 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(74, 20, 140, 0.05);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #333;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .surat-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        .surat-table td {
            vertical-align: middle;
        }
        
        .action-buttons .btn {
            margin-right: 5px;
        }
        
        .no-data {
            padding: 3rem 1rem;
            text-align: center;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="spinner-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3">Memuat data...</p>
        </div>
    </div>
    
    <!-- Overlay Mobile -->
    <div class="overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <!-- Sidebar Header -->
        <div class="p-3 border-bottom border-white border-opacity-10">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                     style="width: 40px; height: 40px; color: var(--primary-color);">
                    <i class="bi bi-envelope-paper fs-5"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold" style="font-size: 1.1rem;">Sistem Surat</h4>
                    <small class="text-white-50">Polibatam</small>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-grow-1 p-3">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-white d-flex align-items-center active" onclick="showDashboard()">
                        <i class="bi bi-house-door me-3 fs-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-white d-flex align-items-center" onclick="showRiwayat()">
                        <i class="bi bi-clock-history me-3 fs-5"></i>
                        <span>Riwayat Surat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white d-flex align-items-center" onclick="showNotifikasi()">
                        <i class="bi bi-bell me-3 fs-5"></i>
                        <span>Notifikasi</span>
                        <?php if ($notifikasi_count > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo $notifikasi_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- User Info & Logout -->
        <div class="p-3 border-top border-white border-opacity-10">
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                     style="width: 50px; height: 50px; color: var(--primary-color); font-weight: 600; font-size: 1.3rem;">
                    <?php echo isset($current_mahasiswa['nama']) ? strtoupper(substr($current_mahasiswa['nama'], 0, 1)) : 'M'; ?>
                </div>
                <div class="ms-3">
                    <div class="fw-bold"><?php echo htmlspecialchars($current_mahasiswa['nama']); ?></div>
                    <small class="text-white-50">Mahasiswa</small>
                    <div class="small"><?php echo htmlspecialchars($current_mahasiswa['nim']); ?></div>
                </div>
            </div>
            <button class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center" onclick="logout()">
                <i class="bi bi-box-arrow-right me-2"></i>Keluar
            </button>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-outline-primary d-lg-none me-2" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                
                <h1 class="h4 mb-0 fw-bold" id="pageTitle">Dashboard Mahasiswa</h1>
                
                <div class="d-flex align-items-center">
                    <!-- Notifikasi Bell -->
                    <div class="position-relative me-3">
                        <button class="btn btn-outline-primary position-relative" onclick="showNotifikasi()">
                            <i class="bi bi-bell"></i>
                            <?php if ($notifikasi_count > 0): ?>
                            <span class="notification-badge"><?php echo $notifikasi_count; ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    
                    <div class="me-3 text-end d-none d-md-block">
                        <div id="currentDate" class="fw-bold small"><?php echo date('l, d F Y'); ?></div>
                        <div id="currentTime" class="text-muted small"><?php echo date('H:i:s'); ?></div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="toggleTheme()">
                        <i class="bi bi-moon" id="themeIcon"></i>
                    </button>
                </div>
            </div>
        </nav>
        
        <!-- Content Area -->
        <div class="container-fluid py-4" id="contentArea">
            <!-- Dashboard Content (Default View) -->
            <div id="dashboardView">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="h3 mb-2 fw-bold">Selamat Datang, <?php echo htmlspecialchars($current_mahasiswa['nama']); ?>!</h2>
                            <p class="mb-0 opacity-75">Monitor dan kelola surat peringatan Anda di sini.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="bg-white text-dark p-3 rounded-3 d-inline-block" style="box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                <div class="small text-muted">NIM</div>
                                <div class="fw-bold fs-5"><?php echo htmlspecialchars($current_mahasiswa['nim']); ?></div>
                                <div class="small text-muted mt-2">Program Studi</div>
                                <div class="fw-bold"><?php echo htmlspecialchars($current_mahasiswa['program_studi']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-primary">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-envelope fs-4 me-2"></i>
                                <span class="fs-6">Total Surat</span>
                            </div>
                            <div class="fs-2 fw-bold" id="statTotalSurat"><?php echo $total_surat; ?></div>
                            <small class="opacity-75">Semua surat peringatan</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-success">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle fs-4 me-2"></i>
                                <span class="fs-6">Disetujui</span>
                            </div>
                            <div class="fs-2 fw-bold" id="statApprovedSurat"><?php echo $surat_disetujui; ?></div>
                            <small class="opacity-75">Surat yang sudah valid</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-warning">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-clock-history fs-4 me-2"></i>
                                <span class="fs-6">Menunggu</span>
                            </div>
                            <div class="fs-2 fw-bold" id="statPendingSurat"><?php echo $pending_surat; ?></div>
                            <small class="opacity-75">Dalam proses verifikasi</small>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card bg-info">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-bell fs-4 me-2"></i>
                                <span class="fs-6">Surat Baru</span>
                            </div>
                            <div class="fs-2 fw-bold" id="statNotifikasi"><?php echo $notifikasi_count; ?></div>
                            <small class="opacity-75">7 hari terakhir</small>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Surat & Notifikasi -->
                <div class="row">
                    <!-- Recent Surat -->
                    <div class="col-lg-8 mb-4">
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0 fw-bold text-primary">
                                    <i class="bi bi-clock-history me-2"></i>Surat Terbaru
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                            
                            <div id="recentSuratList">
                                <?php if (count($recent_surat) > 0): ?>
                                    <?php foreach ($recent_surat as $surat): ?>
                                        <?php
                                        $status_class = $surat['status'] == 'approved' ? 'bg-success' : 
                                                      ($surat['status'] == 'rejected' ? 'bg-danger' : 'bg-warning');
                                        $status_text = $surat['status'] == 'approved' ? 'Disetujui' : 
                                                     ($surat['status'] == 'rejected' ? 'Ditolak' : 'Menunggu');
                                        $is_new = false;
                                        if ($surat['status'] == 'approved') {
                                            $updated_date = date('Y-m-d', strtotime($surat['updated_at']));
                                            if ($updated_date >= $seven_days_ago) {
                                                $is_new = true;
                                            }
                                        }
                                        ?>
                                        <div class="recent-item" onclick="previewSurat(<?php echo $surat['id']; ?>)">
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($surat['nomor_surat']); ?></div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo date('d F Y', strtotime($surat['tanggal_surat'])); ?>
                                                </div>
                                                <div class="small mt-1">
                                                    <i class="bi bi-tag me-1"></i>
                                                    <?php echo htmlspecialchars($surat['jenis_pelanggaran']); ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge <?php echo $status_class; ?> me-2"><?php echo $status_text; ?></span>
                                                <?php if ($is_new): ?>
                                                <span class="badge bg-danger new-badge"><i class="bi bi-star-fill"></i> Baru</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada surat peringatan</p>
                                    <small class="text-muted">Semua surat akan muncul di sini</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="showRiwayat()">
                                    <i class="bi bi-eye me-1"></i>Lihat Semua Surat
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifikasi Terbaru -->
                    <div class="col-lg-4 mb-4">
                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0 fw-bold text-primary">
                                    <i class="bi bi-bell me-2"></i>Surat Disetujui
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            
                            <div id="notificationList">
                                <?php if (count($notifikasi_data) > 0): ?>
                                    <?php foreach ($notifikasi_data as $notif): ?>
                                        <?php
                                        $days_ago = floor((time() - strtotime($notif['updated_at'])) / (60 * 60 * 24));
                                        $time_text = $days_ago == 0 ? 'Hari ini' : 
                                                   ($days_ago == 1 ? 'Kemarin' : $days_ago . ' hari lalu');
                                        ?>
                                        <div class="notification-item unread" onclick="previewSurat(<?php echo $notif['id']; ?>)">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="bi bi-envelope-check text-primary fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($notif['nomor_surat']); ?></div>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        Surat telah disetujui
                                                    </div>
                                                    <div class="small text-muted mt-2">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo $time_text; ?> • 
                                                        <?php echo date('H:i', strtotime($notif['updated_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-bell-slash fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Tidak ada surat baru</p>
                                    <small class="text-muted">Surat yang disetujui akan muncul di sini</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-primary btn-sm" onclick="showNotifikasi()">
                                    <i class="bi bi-list me-1"></i>Lihat Semua Notifikasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Riwayat View (Hidden by default) -->
            <div id="riwayatView" style="display: none;">
                <div class="card-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-clock-history me-2"></i>Riwayat Surat Peringatan
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="showDashboard()">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </button>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus" onchange="filterRiwayat()">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" id="filterJenis" onchange="filterRiwayat()">
                                <option value="">Semua Jenis</option>
                                <option value="akademik">Akademik</option>
                                <option value="etika">Etika</option>
                                <option value="administrasi">Administrasi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="filterStartDate" onchange="filterRiwayat()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="filterEndDate" onchange="filterRiwayat()">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover surat-table">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Surat</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="riwayatTableBody">
                                <?php if (count($all_surat) > 0): ?>
                                    <?php foreach ($all_surat as $surat): ?>
                                        <?php
                                        $status_class = $surat['status'] == 'approved' ? 'bg-success' : 
                                                      ($surat['status'] == 'rejected' ? 'bg-danger' : 'bg-warning');
                                        $status_text = $surat['status'] == 'approved' ? 'Disetujui' : 
                                                     ($surat['status'] == 'rejected' ? 'Ditolak' : 'Menunggu');
                                        $jenis_text = $surat['jenis_pelanggaran'];
                                        $tanggal = date('d F Y', strtotime($surat['tanggal_surat']));
                                        $is_new = $surat['status'] == 'approved' && 
                                                 date('Y-m-d', strtotime($surat['updated_at'])) >= $seven_days_ago;
                                        ?>
                                        <tr data-status="<?php echo $surat['status']; ?>" 
                                            data-jenis="<?php echo $surat['jenis_pelanggaran']; ?>"
                                            data-tanggal="<?php echo $surat['tanggal_surat']; ?>">
                                            <td class="fw-bold"><?php echo htmlspecialchars($surat['nomor_surat']); ?></td>
                                            <td><?php echo $tanggal; ?></td>
                                            <td><?php echo htmlspecialchars($jenis_text); ?></td>
                                            <td>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                    <?php if ($is_new): ?>
                                                    <i class="bi bi-star-fill ms-1"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" onclick="previewSurat(<?php echo $surat['id']; ?>)">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="downloadPDF(<?php echo $surat['id']; ?>)">
                                                    <i class="bi bi-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                        <p class="text-muted">Tidak ada riwayat surat</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Notifikasi View (Hidden by default) -->
            <div id="notifikasiView" style="display: none;">
                <div class="card-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-bell me-2"></i>Surat yang Disetujui
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="showDashboard()">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </button>
                    </div>
                    
                    <div id="notifikasiList">
                        <?php 
                        $approved_surat = array_filter($all_surat, function($surat) use ($seven_days_ago) {
                            return $surat['status'] == 'approved' && 
                                   date('Y-m-d', strtotime($surat['updated_at'])) >= $seven_days_ago;
                        });
                        ?>
                        
                        <?php if (count($approved_surat) > 0): ?>
                            <?php foreach ($approved_surat as $surat): ?>
                                <?php
                                $days_ago = floor((time() - strtotime($surat['updated_at'])) / (60 * 60 * 24));
                                $time_text = $days_ago == 0 ? 'Hari ini' : 
                                           ($days_ago == 1 ? 'Kemarin' : $days_ago . ' hari lalu');
                                ?>
                                <div class="notification-item unread mb-3" onclick="previewSurat(<?php echo $surat['id']; ?>)">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="bi bi-envelope-check text-primary fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($surat['nomor_surat']); ?></div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        Surat peringatan telah disetujui
                                                    </small>
                                                </div>
                                                <small class="text-muted"><?php echo $time_text; ?></small>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-tag me-1"></i>
                                                    Jenis: <?php echo htmlspecialchars($surat['jenis_pelanggaran']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-danger mt-2">
                                                <i class="bi bi-star-fill me-1"></i>BARU
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">Tidak ada surat yang disetujui</p>
                            <small class="text-muted">Surat yang disetujui dalam 7 hari terakhir akan muncul di sini</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODALS ==================== -->
    
    <!-- Modal Preview Surat -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope-paper me-2"></i>Detail Surat Peringatan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent">
                        <!-- Content akan diisi oleh JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printPreview()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Global variables
        let currentSuratId = null;
        let previewModal = null;
        
        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            initClock();
            initTheme();
            
            // Initialize modal
            previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            
            // Initialize event listeners
            document.getElementById('filterStatus').addEventListener('change', filterRiwayat);
            document.getElementById('filterJenis').addEventListener('change', filterRiwayat);
            document.getElementById('filterStartDate').addEventListener('change', filterRiwayat);
            document.getElementById('filterEndDate').addEventListener('change', filterRiwayat);
            
            // Simulate checking for new notifications every 30 seconds
            setInterval(simulateCheckNewNotifications, 30000);
        });
        
        // Initialize clock
        function initClock() {
            updateClock();
            setInterval(updateClock, 1000);
        }
        
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID');
        }
        
        // Theme functions
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                enableDarkMode();
            }
        }
        
        function toggleTheme() {
            if (document.body.classList.contains('dark-mode')) {
                disableDarkMode();
                localStorage.setItem('theme', 'light');
            } else {
                enableDarkMode();
                localStorage.setItem('theme', 'dark');
            }
        }
        
        function enableDarkMode() {
            document.body.classList.add('dark-mode');
            document.getElementById('themeIcon').className = 'bi bi-sun';
        }
        
        function disableDarkMode() {
            document.body.classList.remove('dark-mode');
            document.getElementById('themeIcon').className = 'bi bi-moon';
        }
        
        // Sidebar functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        
        // Navigation functions - TIDAK PERLU AJAX LAGI
        function showDashboard() {
            document.getElementById('dashboardView').style.display = 'block';
            document.getElementById('riwayatView').style.display = 'none';
            document.getElementById('notifikasiView').style.display = 'none';
            document.getElementById('pageTitle').textContent = 'Dashboard Mahasiswa';
            updateActiveNav('dashboard');
        }
        
        function showRiwayat() {
            document.getElementById('dashboardView').style.display = 'none';
            document.getElementById('riwayatView').style.display = 'block';
            document.getElementById('notifikasiView').style.display = 'none';
            document.getElementById('pageTitle').textContent = 'Riwayat Surat';
            updateActiveNav('riwayat');
        }
        
        function showNotifikasi() {
            document.getElementById('dashboardView').style.display = 'none';
            document.getElementById('riwayatView').style.display = 'none';
            document.getElementById('notifikasiView').style.display = 'block';
            document.getElementById('pageTitle').textContent = 'Surat Disetujui';
            updateActiveNav('notifikasi');
        }
        
        function updateActiveNav(page) {
            // Reset all nav items
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Highlight current page
            let selector = '';
            if (page === 'dashboard') {
                selector = 'a[onclick*="showDashboard"]';
            } else if (page === 'riwayat') {
                selector = 'a[onclick*="showRiwayat"]';
            } else if (page === 'notifikasi') {
                selector = 'a[onclick*="showNotifikasi"]';
            }
            
            const currentLink = document.querySelector(selector);
            if (currentLink) {
                currentLink.classList.add('active');
            }
        }
        
        // Loading functions
        function showLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('show', show);
        }
        
        // Filter functions - SEMUA DILAKUKAN DI CLIENT SIDE
        function filterRiwayat() {
            const statusFilter = document.getElementById('filterStatus').value;
            const jenisFilter = document.getElementById('filterJenis').value;
            const startDateFilter = document.getElementById('filterStartDate').value;
            const endDateFilter = document.getElementById('filterEndDate').value;
            
            const rows = document.querySelectorAll('#riwayatTableBody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                if (row.cells.length < 5) return; // Skip placeholder rows
                
                const status = row.getAttribute('data-status');
                const jenis = row.getAttribute('data-jenis');
                const tanggal = row.getAttribute('data-tanggal');
                
                let showRow = true;
                
                // Filter by status
                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }
                
                // Filter by jenis
                if (jenisFilter && jenis !== jenisFilter) {
                    showRow = false;
                }
                
                // Filter by date range
                if (startDateFilter && tanggal < startDateFilter) {
                    showRow = false;
                }
                
                if (endDateFilter && tanggal > endDateFilter) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show no data message if no rows visible
            const tbody = document.getElementById('riwayatTableBody');
            let noDataRow = tbody.querySelector('.no-data-row');
            
            if (visibleCount === 0) {
                if (!noDataRow) {
                    noDataRow = document.createElement('tr');
                    noDataRow.className = 'no-data-row';
                    noDataRow.innerHTML = `
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted">Tidak ada data yang sesuai dengan filter</p>
                        </td>
                    `;
                    tbody.appendChild(noDataRow);
                }
                noDataRow.style.display = '';
            } else if (noDataRow) {
                noDataRow.style.display = 'none';
            }
        }
        
        // Refresh functions
        function refreshDashboard() {
            showLoading(true);
            
            // Simulate refresh with timeout
            setTimeout(() => {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Dashboard Diperbarui',
                    text: 'Data dashboard telah diperbarui',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                
                showLoading(false);
            }, 500);
        }
        
        // Simulate checking for new notifications
        function simulateCheckNewNotifications() {
            // This is just a simulation - in real app, you would check with server
            const hasNewNotifications = Math.random() > 0.7; // 30% chance
            
            if (hasNewNotifications) {
                Swal.fire({
                    icon: 'info',
                    title: 'Pemberitahuan',
                    text: 'Periksa notifikasi untuk update terbaru',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        }
        
        // Preview functions - TIDAK PERLU AJAX
        function previewSurat(suratId) {
            showLoading(true);
            currentSuratId = suratId;
            
            // Create example surat content based on ID
            const suratContent = createSuratContent(suratId);
            
            setTimeout(() => {
                document.getElementById('previewContent').innerHTML = suratContent;
                previewModal.show();
                showLoading(false);
            }, 300);
        }
        
        function createSuratContent(suratId) {
            // This is example content - in real app, you would get this from database
            const suratExamples = {
                1: {
                    nomor: "SP/001/IX/2023",
                    tanggal: "15 September 2023",
                    jenis: "Akademik",
                    status: "Disetujui",
                    deskripsi: "Mahasiswa terlambat mengumpulkan tugas mata kuliah Pemrograman Web sebanyak 3 kali berturut-turut sesuai dengan jadwal yang telah ditentukan.",
                    sanksi: [
                        "Pengurangan nilai tugas sebesar 30%",
                        "Kewajiban membuat surat pernyataan",
                        "Pemanggilan orang tua/wali"
                    ]
                },
                2: {
                    nomor: "SP/002/X/2023",
                    tanggal: "05 Oktober 2023",
                    jenis: "Etika",
                    status: "Menunggu",
                    deskripsi: "Mahasiswa tidak mematuhi peraturan berpakaian yang telah ditetapkan selama kegiatan perkuliahan.",
                    sanksi: [
                        "Teguran lisan",
                        "Pembinaan khusus oleh dosen wali"
                    ]
                }
            };
            
            const surat = suratExamples[suratId] || {
                nomor: "SP/XXX/MM/YYYY",
                tanggal: "<?php echo date('d F Y'); ?>",
                jenis: "Contoh",
                status: "Disetujui",
                deskripsi: "Ini adalah contoh surat peringatan.",
                sanksi: ["Contoh sanksi 1", "Contoh sanksi 2"]
            };
            
            return `
                <div class="container">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-primary">SURAT PERINGATAN</h4>
                        <h5 class="fw-bold">Nomor: ${surat.nomor}</h5>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Nama Mahasiswa</th>
                                    <td><?php echo htmlspecialchars($current_mahasiswa['nama']); ?></td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td><?php echo htmlspecialchars($current_mahasiswa['nim']); ?></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td><?php echo htmlspecialchars($current_mahasiswa['program_studi']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Tanggal Surat</th>
                                    <td>${surat.tanggal}</td>
                                </tr>
                                <tr>
                                    <th>Jenis Pelanggaran</th>
                                    <td><span class="badge bg-warning">${surat.jenis}</span></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge bg-success">${surat.status}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">DESKRIPSI PELANGGARAN</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${surat.deskripsi}</p>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold">SANKSI</h6>
                        </div>
                        <div class="card-body">
                            <ol>
                                ${surat.sanksi.map(item => `<li>${item}</li>`).join('')}
                            </ol>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informasi:</strong> ${surat.status === 'Disetujui' ? 
                            'Surat ini telah disetujui dan berlaku resmi.' : 
                            'Surat ini masih dalam proses peninjauan.'}
                    </div>
                </div>
            `;
        }
        
        function printPreview() {
            const printContent = document.getElementById('previewContent').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Cetak Surat Peringatan</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        @media print {
                            .no-print { display: none; }
                            body { margin: 0; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <div class="no-print text-center mt-4">
                        <button onclick="window.close()" class="btn btn-primary">Tutup</button>
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                        }
                    <\/script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
        }
        
        function downloadPDF(suratId) {
            Swal.fire({
                title: 'Download PDF',
                text: 'Fitur download PDF akan segera tersedia',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
        
        // Logout function
        function logout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4a148c',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
        
        // Add dark mode CSS
        const darkModeCSS = `
            <style>
                body.dark-mode {
                    background-color: #121212;
                    color: #e0e0e0;
                }
                body.dark-mode .sidebar {
                    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
                }
                body.dark-mode .navbar {
                    background-color: #1e1e1e !important;
                    border-bottom-color: #333 !important;
                }
                body.dark-mode .card-content {
                    background-color: #1e1e1e !important;
                    color: #e0e0e0;
                    border-color: #333;
                }
                body.dark-mode .text-muted {
                    color: #aaa !important;
                }
                body.dark-mode .table {
                    color: #e0e0e0;
                    border-color: #444;
                }
                body.dark-mode .table-light {
                    background-color: #2a2a2a !important;
                }
                body.dark-mode .form-control, 
                body.dark-mode .form-select {
                    background-color: #2a2a2a;
                    border-color: #444;
                    color: #e0e0e0;
                }
                body.dark-mode .recent-item:hover {
                    background-color: #2a2a2a;
                }
                body.dark-mode .notification-item:hover {
                    background-color: #2a2a2a;
                }
                body.dark-mode .notification-item.unread {
                    background-color: #1a365d;
                }
                body.dark-mode .profile-header {
                    background: linear-gradient(135deg, #2d1b69, #4a148c);
                }
                body.dark-mode .stat-card {
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                }
            </style>
        `;
        
        // Add dark mode CSS to head
        document.head.insertAdjacentHTML('beforeend', darkModeCSS);
    </script>
</body>
</html>
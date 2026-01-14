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
require_once "auth_koneksi/koneksi.php";

// Ambil data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$current_user = mysqli_fetch_assoc($result_user);

// Hitung statistik
$total_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan"))['total'];
$pending_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'pending'"))['total'];
$surat_disetujui = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan WHERE status = 'approved'"))['total'];
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'aktif'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sistem Surat Peringatan Polibatam</title>
    
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
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
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
            border-radius: 10px;
            color: white;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .action-btn:hover {
            border-color: #0d6efd;
            background: #e7f1ff;
            transform: translateY(-3px);
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .recent-item:hover {
            background-color: #f8f9fa;
        }
        
        .table-actions {
            min-width: 150px;
        }
        
        /* Animasi loading */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        
        .spinner-overlay.show {
            display: flex;
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
            <h4 class="mb-1 fw-bold">Surat Peringatan</h4>
            <small class="text-white-50">Politeknik Negeri Batam</small>
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
                    <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('arsip')">
                        <i class="bi bi-archive me-3 fs-5"></i>
                        <span>Arsip Surat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('mahasiswa')">
                        <i class="bi bi-people me-3 fs-5"></i>
                        <span>Data Mahasiswa</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- User Info & Logout -->
        <div class="p-3 border-top border-white border-opacity-10">
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center" 
                     style="width: 45px; height: 45px; font-weight: 600; font-size: 1.2rem;">
                    <?php echo isset($current_user['nama']) ? strtoupper(substr($current_user['nama'], 0, 1)) : 'A'; ?>
                </div>
                <div class="ms-3">
                    <div class="fw-bold"><?php echo htmlspecialchars($current_user['nama']); ?></div>
                    <small class="text-white-50">Administrator</small>
                </div>
            </div>
            <button class="btn btn-outline-light w-100" onclick="logout()">
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
                
                <h1 class="h4 mb-0 fw-bold" id="pageTitle">Dashboard</h1>
                
                <div class="d-flex align-items-center">
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
        
        <!-- Content -->
        <div class="container-fluid py-3">
            <!-- Dashboard Content -->
            <div id="dashboardContent">
                <!-- Welcome & Stats -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="p-4 mb-4 bg-white rounded-3 shadow-sm">
                            <h2 class="h4 mb-2 fw-bold">Selamat Datang, <?php echo htmlspecialchars($current_user['nama']); ?>!</h2>
                            <p class="text-muted mb-4">Kelola sistem surat peringatan dengan mudah.</p>
                            
                            <div class="row g-3">
                                <div class="col-xl-3 col-md-6">
                                    <div class="stat-card bg-primary">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-envelope fs-4 me-2"></i>
                                            <span class="fs-6">Total Surat</span>
                                        </div>
                                        <div class="fs-2 fw-bold" id="statTotalSurat"><?php echo $total_surat; ?></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="stat-card bg-success">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-check-circle fs-4 me-2"></i>
                                            <span class="fs-6">Disetujui</span>
                                        </div>
                                        <div class="fs-2 fw-bold" id="statApprovedSurat"><?php echo $surat_disetujui; ?></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="stat-card bg-warning">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-clock-history fs-4 me-2"></i>
                                            <span class="fs-6">Menunggu</span>
                                        </div>
                                        <div class="fs-2 fw-bold" id="statPendingSurat"><?php echo $pending_surat; ?></div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="stat-card bg-info">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-people fs-4 me-2"></i>
                                            <span class="fs-6">Mahasiswa Aktif</span>
                                        </div>
                                        <div class="fs-2 fw-bold" id="statTotalMahasiswa"><?php echo $total_mahasiswa; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions & Recent -->
                <div class="row">
                    <!-- Quick Actions -->
                    <div class="col-lg-8 mb-4">
                        <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                            <h5 class="mb-4 fw-bold">
                                <i class="bi bi-lightning-charge text-primary me-2"></i>Tindakan Cepat
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <button class="action-btn" onclick="showSuratModal()">
                                        <i class="bi bi-plus-circle fs-1 text-primary mb-2"></i>
                                        <span class="fw-bold">Buat Surat Baru</span>
                                    </button>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <button class="action-btn" onclick="showTemplateModal()">
                                        <i class="bi bi-file-earmark-text fs-1 text-primary mb-2"></i>
                                        <span class="fw-bold">Template Surat</span>
                                    </button>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <button class="action-btn" onclick="loadPage('arsip')">
                                        <i class="bi bi-eye fs-1 text-primary mb-2"></i>
                                        <span class="fw-bold">Lihat Arsip</span>
                                    </button>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <button class="action-btn" onclick="showMahasiswaModal()">
                                        <i class="bi bi-person-plus fs-1 text-primary mb-2"></i>
                                        <span class="fw-bold">Tambah Mahasiswa</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Surat -->
                    <div class="col-lg-4 mb-4">
                        <div class="bg-white p-4 rounded-3 shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-clock text-primary me-2"></i>Surat Terbaru
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshRecentSurat()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            
                            <div id="recentSuratList">
                                <?php
                                $query_surat = "SELECT s.*, m.nama as nama_mahasiswa, m.nim 
                                               FROM surat_peringatan s 
                                               JOIN mahasiswa m ON s.mahasiswa_id = m.id 
                                               ORDER BY s.created_at DESC LIMIT 5";
                                $result_surat = mysqli_query($koneksi, $query_surat);
                                
                                if (mysqli_num_rows($result_surat) > 0) {
                                    while ($surat = mysqli_fetch_assoc($result_surat)) {
                                        $status_class = $surat['status'] == 'approved' ? 'bg-success' : 
                                                      ($surat['status'] == 'rejected' ? 'bg-danger' : 'bg-warning');
                                        $status_text = $surat['status'] == 'approved' ? 'Disetujui' : 
                                                     ($surat['status'] == 'rejected' ? 'Ditolak' : 'Menunggu');
                                ?>
                                <div class="recent-item" onclick="previewSurat(<?php echo $surat['id']; ?>)">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($surat['nomor_surat']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($surat['nama_mahasiswa']); ?> • <?php echo $surat['nim']; ?></small>
                                    </div>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                                <?php
                                    }
                                } else {
                                ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada surat peringatan</p>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dynamic Content (Hidden by default) -->
            <div id="dynamicContent" style="display: none;"></div>
        </div>
    </div>

    <!-- ==================== MODALS ==================== -->
    
    <!-- Modal Surat Peringatan -->
    <div class="modal fade" id="suratModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="suratModalTitle">Buat Surat Peringatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSuratPeringatan" novalidate onsubmit="handleSuratSubmit(event)">
                    <div class="modal-body">
                        <input type="hidden" id="surat_id" name="surat_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                                <div class="form-text">Contoh: SP/2023/001</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                                <select class="form-select" id="mahasiswa_id" name="mahasiswa_id" required>
                                    <option value="">Pilih Mahasiswa</option>
                                    <?php
                                    $mahasiswa_query = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE status = 'aktif' ORDER BY nama");
                                    while ($mhs = mysqli_fetch_assoc($mahasiswa_query)) {
                                        echo "<option value='{$mhs['id']}'>{$mhs['nama']} ({$mhs['nim']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Jenis Pelanggaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_pelanggaran" name="jenis_pelanggaran" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="akademik">Akademik</option>
                                    <option value="etika">Etika</option>
                                    <option value="administrasi">Administrasi</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pelanggaran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_pelanggaran" name="tanggal_pelanggaran" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3" required></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Sanksi <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="sanksi" name="sanksi" rows="2" required></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Penandatangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penandatangan" name="penandatangan" required 
                                       value="<?php echo htmlspecialchars($current_user['nama']); ?>">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Menunggu</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="suratSubmitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="suratSpinner"></span>
                            <span id="suratBtnText">Simpan Surat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Template Surat -->
    <div class="modal fade" id="templateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Pilih Template Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary w-100 h-100 p-4 d-flex flex-column align-items-center" onclick="applyTemplate('akademik')">
                                <i class="bi bi-book fs-1 mb-3"></i>
                                <span class="fw-bold">Template Akademik</span>
                                <small class="text-muted mt-2 text-center">Untuk pelanggaran akademik</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-warning w-100 h-100 p-4 d-flex flex-column align-items-center" onclick="applyTemplate('etika')">
                                <i class="bi bi-person-badge fs-1 mb-3"></i>
                                <span class="fw-bold">Template Etika</span>
                                <small class="text-muted mt-2 text-center">Untuk pelanggaran etika</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-info w-100 h-100 p-4 d-flex flex-column align-items-center" onclick="applyTemplate('administrasi')">
                                <i class="bi bi-files fs-1 mb-3"></i>
                                <span class="fw-bold">Template Administrasi</span>
                                <small class="text-muted mt-2 text-center">Untuk kelalaian administrasi</small>
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-secondary w-100 h-100 p-4 d-flex flex-column align-items-center" onclick="applyTemplate('umum')">
                                <i class="bi bi-file-text fs-1 mb-3"></i>
                                <span class="fw-bold">Template Umum</span>
                                <small class="text-muted mt-2 text-center">Template umum surat peringatan</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Mahasiswa -->
    <div class="modal fade" id="mahasiswaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mahasiswaModalTitle">Tambah Mahasiswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMahasiswa" novalidate onsubmit="handleMahasiswaSubmit(event)">
                    <div class="modal-body">
                        <input type="hidden" id="mahasiswa_id_edit" name="mahasiswa_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_mahasiswa" name="nama" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nim_mahasiswa" name="nim" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-select" id="prodi_mahasiswa" name="program_studi" required>
                                    <option value="">Pilih Prodi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Elektro">Teknik Elektro</option>
                                    <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="semester_mahasiswa" name="semester" min="1" max="14" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username_mahasiswa" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span id="passwordRequired" class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_mahasiswa" name="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status_mahasiswa" name="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                    <option value="cuti">Cuti</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="mahasiswaSubmitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="mahasiswaSpinner"></span>
                            <span id="mahasiswaBtnText">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Preview Surat -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Preview Surat Peringatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent" class="p-3">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printPreview()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                    <button type="button" class="btn btn-success" onclick="downloadPreviewPDF()">
                        <i class="bi bi-download me-2"></i>Download PDF
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
        let currentMahasiswaId = null;
        let suratModal = new bootstrap.Modal(document.getElementById('suratModal'));
        let templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
        let mahasiswaModal = new bootstrap.Modal(document.getElementById('mahasiswaModal'));
        let previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        
        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            initClock();
            initTheme();
            initEventListeners();
            setDefaultDates();
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
        
        // Initialize theme
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
        
        function initEventListeners() {
            // Set default dates for forms
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_surat').value = today;
            document.getElementById('tanggal_pelanggaran').value = today;
        }
        
        // Navigation functions
        function showDashboard() {
            document.getElementById('dashboardContent').style.display = 'block';
            document.getElementById('dynamicContent').style.display = 'none';
            document.getElementById('pageTitle').textContent = 'Dashboard';
            updateActiveNav('dashboard');
        }
        
        function loadPage(page) {
            showLoading(true);
            
            document.getElementById('dashboardContent').style.display = 'none';
            document.getElementById('dynamicContent').style.display = 'block';
            
            const titles = {
                'arsip': 'Arsip Surat',
                'mahasiswa': 'Data Mahasiswa'
            };
            
            document.getElementById('pageTitle').textContent = titles[page];
            updateActiveNav(page);
            
            setTimeout(() => {
                if (page === 'arsip') {
                    loadArsipContent();
                } else if (page === 'mahasiswa') {
                    loadMahasiswaContent();
                }
                showLoading(false);
            }, 300);
        }
        
        function updateActiveNav(page) {
            // Reset all nav items
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Highlight current page
            const navId = page === 'dashboard' ? 'dashboard' : page;
            // The navigation is handled by onclick events in the HTML
        }
        
        // Loading functions
        function showLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('show', show);
        }
        
        // Form functions
        function setDefaultDates() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_surat').value = today;
            document.getElementById('tanggal_pelanggaran').value = today;
        }
        
        // Modal functions
        function showSuratModal(suratId = null) {
            const form = document.getElementById('formSuratPeringatan');
            form.reset();
            form.classList.remove('was-validated');
            
            if (suratId) {
                document.getElementById('suratModalTitle').textContent = 'Edit Surat Peringatan';
                document.getElementById('surat_id').value = suratId;
                loadSuratData(suratId);
            } else {
                document.getElementById('suratModalTitle').textContent = 'Buat Surat Peringatan';
                document.getElementById('surat_id').value = '';
                generateNomorSurat();
                setDefaultDates();
                document.getElementById('penandatangan').value = '<?php echo htmlspecialchars($current_user['nama']); ?>';
            }
            
            suratModal.show();
        }
        
        function showTemplateModal() {
            templateModal.show();
        }
        
        function showMahasiswaModal(mahasiswaId = null) {
            const form = document.getElementById('formMahasiswa');
            form.reset();
            form.classList.remove('was-validated');
            
            if (mahasiswaId) {
                document.getElementById('mahasiswaModalTitle').textContent = 'Edit Data Mahasiswa';
                document.getElementById('mahasiswa_id_edit').value = mahasiswaId;
                document.getElementById('passwordRequired').style.display = 'none';
                loadMahasiswaData(mahasiswaId);
            } else {
                document.getElementById('mahasiswaModalTitle').textContent = 'Tambah Mahasiswa';
                document.getElementById('mahasiswa_id_edit').value = '';
                document.getElementById('passwordRequired').style.display = 'inline';
                document.getElementById('password_mahasiswa').required = true;
            }
            
            mahasiswaModal.show();
        }
        
        // Data loading functions
        function loadSuratData(id) {
            showLoading(true);
            fetch(`api/get_surat.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const surat = data.data;
                        document.getElementById('nomor_surat').value = surat.nomor_surat;
                        document.getElementById('mahasiswa_id').value = surat.mahasiswa_id;
                        document.getElementById('jenis_pelanggaran').value = surat.jenis_pelanggaran;
                        document.getElementById('keterangan').value = surat.keterangan;
                        document.getElementById('sanksi').value = surat.sanksi;
                        document.getElementById('tanggal_pelanggaran').value = surat.tanggal_pelanggaran.split(' ')[0];
                        document.getElementById('tanggal_surat').value = surat.tanggal_surat.split(' ')[0];
                        document.getElementById('penandatangan').value = surat.penandatangan;
                        document.getElementById('status').value = surat.status;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Gagal memuat data surat', 'error');
                })
                .finally(() => showLoading(false));
        }
        
        function loadMahasiswaData(id) {
            showLoading(true);
            fetch(`api/get_mahasiswa.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const mhs = data.data;
                        document.getElementById('nama_mahasiswa').value = mhs.nama;
                        document.getElementById('nim_mahasiswa').value = mhs.nim;
                        document.getElementById('prodi_mahasiswa').value = mhs.program_studi;
                        document.getElementById('semester_mahasiswa').value = mhs.semester;
                        document.getElementById('username_mahasiswa').value = mhs.username;
                        document.getElementById('status_mahasiswa').value = mhs.status;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'Gagal memuat data mahasiswa', 'error');
                })
                .finally(() => showLoading(false));
        }
        
        // Form submission handlers
        function handleSuratSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const isEdit = document.getElementById('surat_id').value !== '';
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            // Show loading on button
            const submitBtn = document.getElementById('suratSubmitBtn');
            const spinner = document.getElementById('suratSpinner');
            const btnText = document.getElementById('suratBtnText');
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Menyimpan...';
            
            // Prepare form data
            const formData = new FormData(form);
            const url = isEdit ? 'api/update_surat.php' : 'api/tambah_surat.php';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        suratModal.hide();
                        refreshDashboard();
                        if (document.getElementById('dynamicContent').style.display === 'block') {
                            const activeNav = document.querySelector('.nav-link.active');
                            if (activeNav && activeNav.textContent.includes('Arsip')) {
                                loadArsipContent();
                            }
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Simpan Surat';
            });
        }
        
        function handleMahasiswaSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const isEdit = document.getElementById('mahasiswa_id_edit').value !== '';
            
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            // Show loading on button
            const submitBtn = document.getElementById('mahasiswaSubmitBtn');
            const spinner = document.getElementById('mahasiswaSpinner');
            const btnText = document.getElementById('mahasiswaBtnText');
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Menyimpan...';
            
            // Prepare form data
            const formData = new FormData(form);
            const url = isEdit ? 'api/update_mahasiswa.php' : 'api/tambah_mahasiswa.php';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        mahasiswaModal.hide();
                        refreshDashboard();
                        if (document.getElementById('dynamicContent').style.display === 'block') {
                            const activeNav = document.querySelector('.nav-link.active');
                            if (activeNav && activeNav.textContent.includes('Mahasiswa')) {
                                loadMahasiswaContent();
                            }
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan: ' + error, 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Simpan';
            });
        }
        
        // Template functions
        function applyTemplate(template) {
            let keterangan = '';
            let sanksi = '';
            
            switch(template) {
                case 'akademik':
                    keterangan = 'Mahasiswa tidak mengumpulkan tugas mata kuliah minimal 3 kali berturut-turut atau memiliki nilai di bawah standar yang ditetapkan.';
                    sanksi = 'Peringatan tertulis pertama, wajib konsultasi dengan dosen pengampu, dan mengumpulkan semua tugas yang tertunggak dalam waktu 7 hari kerja.';
                    document.getElementById('jenis_pelanggaran').value = 'akademik';
                    break;
                case 'etika':
                    keterangan = 'Mahasiswa melakukan pelanggaran etika seperti terlambat masuk kelas tanpa izin, tidak menghormati dosen atau teman sekelas, atau melakukan tindakan tidak terpuji lainnya.';
                    sanksi = 'Peringatan tertulis, wajib membuat surat pernyataan, dan mengikuti sesi konseling dengan bagian kemahasiswaan.';
                    document.getElementById('jenis_pelanggaran').value = 'etika';
                    break;
                case 'administrasi':
                    keterangan = 'Mahasiswa tidak memenuhi kewajiban administratif seperti keterlambatan pembayaran SPP, tidak melengkapi dokumen administrasi, atau tidak mengikuti prosedur yang ditetapkan.';
                    sanksi = 'Peringatan tertulis, dikenakan denda sesuai ketentuan, dan wajib melengkapi administrasi dalam waktu 3 hari kerja.';
                    document.getElementById('jenis_pelanggaran').value = 'administrasi';
                    break;
                case 'umum':
                    keterangan = 'Mahasiswa melanggar peraturan yang telah ditetapkan oleh institusi.';
                    sanksi = 'Peringatan tertulis dan wajib mengikuti pembinaan.';
                    document.getElementById('jenis_pelanggaran').value = 'lainnya';
                    break;
            }
            
            document.getElementById('keterangan').value = keterangan;
            document.getElementById('sanksi').value = sanksi;
            
            templateModal.hide();
            Swal.fire({
                icon: 'success',
                title: 'Template diterapkan!',
                text: 'Template berhasil diisi ke form.',
                timer: 1500,
                showConfirmButton: false
            });
        }
        
        function generateNomorSurat() {
            const currentYear = new Date().getFullYear();
            fetch(`api/get_nomor_surat.php?year=${currentYear}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('nomor_surat').value = data.nomor_surat;
                    } else {
                        // Fallback
                        const randomNum = Math.floor(Math.random() * 100) + 1;
                        document.getElementById('nomor_surat').value = `SP/${currentYear}/${randomNum.toString().padStart(3, '0')}`;
                    }
                })
                .catch(() => {
                    // Fallback
                    const currentYear = new Date().getFullYear();
                    const randomNum = Math.floor(Math.random() * 100) + 1;
                    document.getElementById('nomor_surat').value = `SP/${currentYear}/${randomNum.toString().padStart(3, '0')}`;
                });
        }
        
        // Content loading functions
        function loadArsipContent() {
            const content = `
                <div class="bg-white rounded-3 shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-archive text-primary me-2"></i>Arsip Surat Peringatan
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="showSuratModal()">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Surat
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshArsip()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus" onchange="filterArsip()">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" id="filterJenis" onchange="filterArsip()">
                                <option value="">Semua Jenis</option>
                                <option value="akademik">Akademik</option>
                                <option value="etika">Etika</option>
                                <option value="administrasi">Administrasi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="filterStartDate" onchange="filterArsip()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="filterEndDate" onchange="filterArsip()">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Surat</th>
                                    <th>Mahasiswa</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="arsipTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('dynamicContent').innerHTML = content;
            loadArsipData();
        }
        
        function loadMahasiswaContent() {
            const content = `
                <div class="bg-white rounded-3 shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-people text-primary me-2"></i>Data Mahasiswa
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="showMahasiswaModal()">
                                <i class="bi bi-person-plus me-1"></i>Tambah Mahasiswa
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshMahasiswa()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="searchMahasiswa" 
                                       placeholder="Cari nama, NIM, atau prodi..." onkeyup="searchMahasiswa()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatusMahasiswa" onchange="filterMahasiswa()">
                                <option value="">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                                <option value="cuti">Cuti</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterProdiMahasiswa" onchange="filterMahasiswa()">
                                <option value="">Semua Prodi</option>
                                <option value="Teknik Informatika">Teknik Informatika</option>
                                <option value="Sistem Informasi">Sistem Informasi</option>
                                <option value="Teknik Elektro">Teknik Elektro</option>
                                <option value="Manajemen Bisnis">Manajemen Bisnis</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Program Studi</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="mahasiswaTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('dynamicContent').innerHTML = content;
            loadMahasiswaDataAll();
        }
        
        function loadArsipData() {
            const status = document.getElementById('filterStatus')?.value || '';
            const jenis = document.getElementById('filterJenis')?.value || '';
            const startDate = document.getElementById('filterStartDate')?.value || '';
            const endDate = document.getElementById('filterEndDate')?.value || '';
            
            let url = `api/get_arsip.php?status=${status}&jenis=${jenis}`;
            if (startDate) url += `&start_date=${startDate}`;
            if (endDate) url += `&end_date=${endDate}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('arsipTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(surat => {
                            const statusClass = getStatusClass(surat.status);
                            const statusText = getStatusText(surat.status);
                            const jenisText = getJenisText(surat.jenis_pelanggaran);
                            const tanggal = formatDate(surat.tanggal_surat);
                            
                            const row = `
                                <tr>
                                    <td>${surat.nomor_surat}</td>
                                    <td>
                                        <div class="fw-bold">${surat.nama_mahasiswa}</div>
                                        <small class="text-muted">${surat.nim}</small>
                                    </td>
                                    <td>${tanggal}</td>
                                    <td>${jenisText}</td>
                                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="previewSurat(${surat.id})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="showSuratModal(${surat.id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="downloadSuratPDF(${surat.id})">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="hapusSurat(${surat.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Tidak ada data surat</p>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading arsip:', error);
                    const tbody = document.getElementById('arsipTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4 text-danger">
                                Gagal memuat data. Silakan refresh halaman.
                            </td>
                        </tr>
                    `;
                });
        }
        
        function loadMahasiswaDataAll() {
            const search = document.getElementById('searchMahasiswa')?.value || '';
            const status = document.getElementById('filterStatusMahasiswa')?.value || '';
            const prodi = document.getElementById('filterProdiMahasiswa')?.value || '';
            
            let url = `api/get_mahasiswa_list.php?search=${encodeURIComponent(search)}&status=${status}&program_studi=${prodi}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('mahasiswaTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(mhs => {
                            const statusClass = getStatusClassMahasiswa(mhs.status);
                            const statusText = getStatusTextMahasiswa(mhs.status);
                            
                            const row = `
                                <tr>
                                    <td>${mhs.nim}</td>
                                    <td>${mhs.nama}</td>
                                    <td>${mhs.program_studi}</td>
                                    <td>${mhs.semester}</td>
                                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" onclick="showMahasiswaModal(${mhs.id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="hapusMahasiswa(${mhs.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">Tidak ada data mahasiswa</p>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading mahasiswa:', error);
                    const tbody = document.getElementById('mahasiswaTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4 text-danger">
                                Gagal memuat data. Silakan refresh halaman.
                            </td>
                        </tr>
                    `;
                });
        }
        
        // Filter functions
        function filterArsip() {
            loadArsipData();
        }
        
        function filterMahasiswa() {
            loadMahasiswaDataAll();
        }
        
        function searchMahasiswa() {
            loadMahasiswaDataAll();
        }
        
        // Refresh functions
        function refreshDashboard() {
            fetch('api/get_recent_surat.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('statTotalSurat').textContent = data.total_surat;
                        document.getElementById('statApprovedSurat').textContent = data.surat_disetujui;
                        document.getElementById('statPendingSurat').textContent = data.pending_surat;
                        document.getElementById('statTotalMahasiswa').textContent = data.total_mahasiswa;
                    }
                })
                .catch(error => console.error('Error refreshing dashboard:', error));
            
            refreshRecentSurat();
        }
        
        function refreshRecentSurat() {
            fetch('api/get_arsip.php?limit=5')
                .then(response => response.json())
                .then(data => {
                    const recentList = document.getElementById('recentSuratList');
                    recentList.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.slice(0, 5).forEach(surat => {
                            const statusClass = getStatusClass(surat.status);
                            const statusText = getStatusText(surat.status);
                            
                            const item = `
                                <div class="recent-item" onclick="previewSurat(${surat.id})">
                                    <div>
                                        <div class="fw-bold">${surat.nomor_surat}</div>
                                        <small class="text-muted">${surat.nama_mahasiswa} • ${surat.nim}</small>
                                    </div>
                                    <span class="badge ${statusClass}">${statusText}</span>
                                </div>
                            `;
                            recentList.innerHTML += item;
                        });
                    } else {
                        recentList.innerHTML = `
                            <div class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                <p class="text-muted">Belum ada surat peringatan</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error refreshing recent surat:', error);
                });
        }
        
        function refreshArsip() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterJenis').value = '';
            document.getElementById('filterStartDate').value = '';
            document.getElementById('filterEndDate').value = '';
            loadArsipData();
        }
        
        function refreshMahasiswa() {
            document.getElementById('searchMahasiswa').value = '';
            document.getElementById('filterStatusMahasiswa').value = '';
            document.getElementById('filterProdiMahasiswa').value = '';
            loadMahasiswaDataAll();
        }
        
        // Preview functions
        function previewSurat(id) {
            showLoading(true);
            currentSuratId = id;
            
            fetch(`api/preview_surat.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('previewContent').innerHTML = html;
                    previewModal.show();
                })
                .catch(error => {
                    Swal.fire('Error', 'Gagal memuat preview surat', 'error');
                })
                .finally(() => showLoading(false));
        }
        
        function printPreview() {
            window.print();
        }
        
        function downloadPreviewPDF() {
            if (currentSuratId) {
                window.open(`api/generate_pdf.php?id=${currentSuratId}`, '_blank');
            }
        }
        
        function downloadSuratPDF(id) {
            window.open(`api/generate_pdf.php?id=${id}`, '_blank');
        }
        
        // Delete functions
        function hapusSurat(id) {
            Swal.fire({
                title: 'Hapus Surat?',
                text: 'Data surat akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading(true);
                    fetch('api/hapus_surat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success');
                            refreshDashboard();
                            if (document.getElementById('dynamicContent').style.display === 'block') {
                                loadArsipData();
                            }
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Gagal menghapus surat', 'error');
                    })
                    .finally(() => showLoading(false));
                }
            });
        }
        
        function hapusMahasiswa(id) {
            Swal.fire({
                title: 'Hapus Mahasiswa?',
                text: 'Data mahasiswa akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading(true);
                    fetch('api/hapus_mahasiswa.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success');
                            refreshDashboard();
                            if (document.getElementById('dynamicContent').style.display === 'block') {
                                loadMahasiswaDataAll();
                            }
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Gagal menghapus mahasiswa', 'error');
                    })
                    .finally(() => showLoading(false));
                }
            });
        }
        
        // Utility functions
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password_mahasiswa');
            const icon = document.querySelector('#mahasiswaModal .btn-outline-secondary i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
        
        function getStatusClass(status) {
            switch(status) {
                case 'approved': return 'bg-success';
                case 'pending': return 'bg-warning';
                case 'rejected': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }
        
        function getStatusText(status) {
            switch(status) {
                case 'approved': return 'Disetujui';
                case 'pending': return 'Menunggu';
                case 'rejected': return 'Ditolak';
                default: return status;
            }
        }
        
        function getJenisText(jenis) {
            switch(jenis) {
                case 'akademik': return 'Akademik';
                case 'etika': return 'Etika';
                case 'administrasi': return 'Administrasi';
                case 'lainnya': return 'Lainnya';
                default: return jenis;
            }
        }
        
        function getStatusClassMahasiswa(status) {
            switch(status) {
                case 'aktif': return 'bg-success';
                case 'nonaktif': return 'bg-danger';
                case 'cuti': return 'bg-warning';
                default: return 'bg-secondary';
            }
        }
        
        function getStatusTextMahasiswa(status) {
            switch(status) {
                case 'aktif': return 'Aktif';
                case 'nonaktif': return 'Nonaktif';
                case 'cuti': return 'Cuti';
                default: return status;
            }
        }
        
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID');
        }
        
        function logout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
        
        // Dark mode CSS
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
                body.dark-mode .bg-white {
                    background-color: #1e1e1e !important;
                    color: #e0e0e0;
                }
                body.dark-mode .text-muted {
                    color: #aaa !important;
                }
                body.dark-mode .table {
                    color: #e0e0e0;
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
            </style>
        `;
        
        // Add dark mode CSS to head
        document.head.insertAdjacentHTML('beforeend', darkModeCSS);
    </script>
</body>
</html>
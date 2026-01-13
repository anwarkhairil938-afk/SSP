<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ambil data statistik
$total_surat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM surat_peringatan"))['total'];
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa WHERE status = 'aktif'"))['total'];

// Data pengaturan default
$pengaturan = [
    'nama_aplikasi' => 'Sistem Surat Peringatan Polibatam',
    'versi_aplikasi' => '1.0.0',
    'nama_institusi' => 'Politeknik Negeri Batam',
    'alamat_institusi' => 'Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461',
    'telp_institusi' => '(0778) 469858',
    'email_institusi' => 'polibatam@polibatam.ac.id',
    'backup_terakhir' => '-',
    'total_surat' => $total_surat,
    'total_mahasiswa' => $total_mahasiswa
];

// Cek file backup terakhir
$backup_dir = "../backups/";
if (file_exists($backup_dir)) {
    $backup_files = glob($backup_dir . "backup-*.json");
    if (!empty($backup_files)) {
        $latest_backup = max($backup_files);
        $pengaturan['backup_terakhir'] = date('d/m/Y H:i', filemtime($latest_backup));
    }
}

echo json_encode(['success' => true, 'data' => $pengaturan]);
?>
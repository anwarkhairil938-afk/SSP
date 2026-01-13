<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$backup_data = [];

// Backup tabel users
$users_query = "SELECT * FROM users";
$users_result = mysqli_query($koneksi, $users_query);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}
$backup_data['users'] = $users;

// Backup tabel mahasiswa
$mahasiswa_query = "SELECT * FROM mahasiswa";
$mahasiswa_result = mysqli_query($koneksi, $query);
$mahasiswa = [];
while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
    $mahasiswa[] = $row;
}
$backup_data['mahasiswa'] = $mahasiswa;

// Backup tabel surat_peringatan
$surat_query = "SELECT * FROM surat_peringatan";
$surat_result = mysqli_query($koneksi, $surat_query);
$surat = [];
while ($row = mysqli_fetch_assoc($surat_result)) {
    $surat[] = $row;
}
$backup_data['surat_peringatan'] = $surat;

// Tambahkan metadata
$backup_data['metadata'] = [
    'backup_time' => date('Y-m-d H:i:s'),
    'total_users' => count($users),
    'total_mahasiswa' => count($mahasiswa),
    'total_surat' => count($surat)
];

// Simpan ke file backup
$backup_dir = "../backups/";
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

$backup_filename = $backup_dir . "backup-" . date('Y-m-d-H-i-s') . ".json";

if (file_put_contents($backup_filename, json_encode($backup_data, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Backup berhasil dibuat', 'data' => $backup_data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat backup']);
}
?>
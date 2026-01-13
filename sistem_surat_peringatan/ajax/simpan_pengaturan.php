<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit();
}

// Dalam contoh ini, kita simpan ke file JSON
// Di implementasi real, bisa disimpan ke database tabel settings
$settings_file = '../config/settings.json';

// Buat direktori jika belum ada
if (!file_exists(dirname($settings_file))) {
    mkdir(dirname($settings_file), 0777, true);
}

// Simpan ke file
if (file_put_contents($settings_file, json_encode($data, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan']);
}
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Jika ada ID, ambil dari database
if (isset($_POST['surat_id']) && !empty($_POST['surat_id'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['surat_id']);
    $query = "SELECT s.*, m.nama as mahasiswa_nama, m.nim, m.program_studi, m.semester 
              FROM surat_peringatan s 
              JOIN mahasiswa m ON s.mahasiswa_id = m.id 
              WHERE s.id = '$id'";
    $result = mysqli_query($koneksi, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
} else {
    // Jika preview dari form, gunakan data POST
    $data = [
        'nomor_surat' => $_POST['nomor_surat'] ?? '',
        'mahasiswa_nama' => 'Nama Mahasiswa',
        'nim' => 'NIM123',
        'program_studi' => 'Program Studi',
        'semester' => '1',
        'jenis_pelanggaran' => $_POST['jenis_pelanggaran'] ?? '',
        'keterangan' => $_POST['keterangan'] ?? '',
        'sanksi' => $_POST['sanksi'] ?? '',
        'tanggal_surat' => $_POST['tanggal_surat'] ?? '',
        'tanggal_pelanggaran' => $_POST['tanggal_pelanggaran'] ?? '',
        'penandatangan' => $_POST['penandatangan'] ?? '',
        'status' => $_POST['status'] ?? 'pending'
    ];
    
    // Jika ada mahasiswa_id, ambil data mahasiswa
    if (isset($_POST['mahasiswa_id']) && !empty($_POST['mahasiswa_id'])) {
        $mahasiswa_id = mysqli_real_escape_string($koneksi, $_POST['mahasiswa_id']);
        $query = "SELECT nama, nim, program_studi, semester FROM mahasiswa WHERE id = '$mahasiswa_id'";
        $result = mysqli_query($koneksi, $query);
        
        if ($mhs = mysqli_fetch_assoc($result)) {
            $data['mahasiswa_nama'] = $mhs['nama'];
            $data['nim'] = $mhs['nim'];
            $data['program_studi'] = $mhs['program_studi'];
            $data['semester'] = $mhs['semester'];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}
?>
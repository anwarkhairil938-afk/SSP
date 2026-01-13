<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Ambil data dari POST
$nomor_surat = mysqli_real_escape_string($koneksi, $_POST['nomor_surat']);
$mahasiswa_id = mysqli_real_escape_string($koneksi, $_POST['mahasiswa_id']);
$jenis_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['jenis_pelanggaran']);
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$sanksi = mysqli_real_escape_string($koneksi, $_POST['sanksi']);
$tanggal_surat = mysqli_real_escape_string($koneksi, $_POST['tanggal_surat']);
$tanggal_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['tanggal_pelanggaran']);
$penandatangan = mysqli_real_escape_string($koneksi, $_POST['penandatangan']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);
$created_by = $_SESSION['user_id'];

$query = "INSERT INTO surat_peringatan (nomor_surat, mahasiswa_id, jenis_pelanggaran, keterangan, sanksi, 
          tanggal_surat, tanggal_pelanggaran, penandatangan, status, created_by, created_at) 
          VALUES ('$nomor_surat', '$mahasiswa_id', '$jenis_pelanggaran', '$keterangan', '$sanksi', 
          '$tanggal_surat', '$tanggal_pelanggaran', '$penandatangan', '$status', '$created_by', NOW())";

if (mysqli_query($koneksi, $query)) {
    $surat_id = mysqli_insert_id($koneksi);
    
    // Handle file upload jika ada
    handleFileUpload($surat_id);
    
    echo json_encode(['success' => true, 'message' => 'Surat berhasil ditambahkan', 'id' => $surat_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan surat: ' . mysqli_error($koneksi)]);
}

function handleFileUpload($surat_id) {
    global $koneksi;
    
    $upload_dir = "../uploads/surat_peringatan/$surat_id/";
    
    // Buat direktori jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Upload image
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $image_name = time() . '_' . basename($_FILES['image_file']['name']);
        $image_path = $upload_dir . $image_name;
        
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $image_path)) {
            $query = "UPDATE surat_peringatan SET bukti_gambar = '$image_name' WHERE id = '$surat_id'";
            mysqli_query($koneksi, $query);
        }
    }
    
    // Upload document
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === 0) {
        $doc_name = time() . '_' . basename($_FILES['document_file']['name']);
        $doc_path = $upload_dir . $doc_name;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $doc_path)) {
            $query = "UPDATE surat_peringatan SET bukti_dokumen = '$doc_name' WHERE id = '$surat_id'";
            mysqli_query($koneksi, $query);
        }
    }
}
?>
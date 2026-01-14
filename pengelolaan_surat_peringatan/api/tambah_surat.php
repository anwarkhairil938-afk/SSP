<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get form data
$nomor_surat = mysqli_real_escape_string($koneksi, $_POST['nomor_surat']);
$mahasiswa_id = intval($_POST['mahasiswa_id']);
$jenis_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['jenis_pelanggaran']);
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$sanksi = mysqli_real_escape_string($koneksi, $_POST['sanksi']);
$tanggal_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['tanggal_pelanggaran']);
$tanggal_surat = mysqli_real_escape_string($koneksi, $_POST['tanggal_surat']);
$penandatangan = mysqli_real_escape_string($koneksi, $_POST['penandatangan']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);

// Handle file upload
$lampiran_filename = null;
$lampiran_path = null;
$lampiran_size = null;

if (isset($_FILES['lampiran_file']) && $_FILES['lampiran_file']['error'] == 0) {
    $uploadDir = '../uploads/';
    
    // Create upload directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['lampiran_file'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    // Allowed file types
    $allowedTypes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/jpg' => 'jpg'
    ];
    
    // Validate file type
    if (array_key_exists($fileType, $allowedTypes)) {
        // Validate file size (max 2MB)
        if ($fileSize <= 2 * 1024 * 1024) {
            // Generate unique filename
            $fileExtension = $allowedTypes[$fileType];
            $newFileName = 'lampiran_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $lampiran_filename = $fileName;
                $lampiran_path = $newFileName;
                $lampiran_size = $fileSize;
            }
        }
    }
}

// Insert into database
$query = "INSERT INTO surat_peringatan 
          (nomor_surat, mahasiswa_id, jenis_pelanggaran, keterangan, sanksi, 
           tanggal_pelanggaran, tanggal_surat, penandatangan, status,
           lampiran_filename, lampiran_path, lampiran_size, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "sisssssssssi", 
    $nomor_surat, $mahasiswa_id, $jenis_pelanggaran, $keterangan, $sanksi,
    $tanggal_pelanggaran, $tanggal_surat, $penandatangan, $status,
    $lampiran_filename, $lampiran_path, $lampiran_size
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Surat berhasil disimpan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan surat: ' . mysqli_error($koneksi)]);
}
?>
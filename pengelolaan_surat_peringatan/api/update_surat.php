<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$surat_id = intval($_POST['surat_id']);
$nomor_surat = mysqli_real_escape_string($koneksi, $_POST['nomor_surat']);
$mahasiswa_id = intval($_POST['mahasiswa_id']);
$jenis_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['jenis_pelanggaran']);
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$sanksi = mysqli_real_escape_string($koneksi, $_POST['sanksi']);
$tanggal_pelanggaran = mysqli_real_escape_string($koneksi, $_POST['tanggal_pelanggaran']);
$tanggal_surat = mysqli_real_escape_string($koneksi, $_POST['tanggal_surat']);
$penandatangan = mysqli_real_escape_string($koneksi, $_POST['penandatangan']);
$status = mysqli_real_escape_string($koneksi, $_POST['status']);
$existing_lampiran = isset($_POST['existing_lampiran']) ? $_POST['existing_lampiran'] : null;

// Handle file upload if new file is uploaded
$lampiran_filename = null;
$lampiran_path = null;
$lampiran_size = null;

if (isset($_FILES['lampiran_file']) && $_FILES['lampiran_file']['error'] == 0) {
    $uploadDir = '../uploads/';
    
    // Delete old file if exists
    if ($existing_lampiran && file_exists($uploadDir . $existing_lampiran)) {
        unlink($uploadDir . $existing_lampiran);
    }
    
    $file = $_FILES['lampiran_file'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    $allowedTypes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/jpg' => 'jpg'
    ];
    
    if (array_key_exists($fileType, $allowedTypes) && $fileSize <= 2 * 1024 * 1024) {
        $fileExtension = $allowedTypes[$fileType];
        $newFileName = 'lampiran_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            $lampiran_filename = $fileName;
            $lampiran_path = $newFileName;
            $lampiran_size = $fileSize;
        }
    }
} else {
    // Keep existing file
    if ($existing_lampiran) {
        $query_get = "SELECT lampiran_filename, lampiran_path, lampiran_size FROM surat_peringatan WHERE id = ?";
        $stmt_get = mysqli_prepare($koneksi, $query_get);
        mysqli_stmt_bind_param($stmt_get, "i", $surat_id);
        mysqli_stmt_execute($stmt_get);
        $result = mysqli_stmt_get_result($stmt_get);
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            $lampiran_filename = $row['lampiran_filename'];
            $lampiran_path = $row['lampiran_path'];
            $lampiran_size = $row['lampiran_size'];
        }
    }
}

// Update database
if ($lampiran_filename) {
    $query = "UPDATE surat_peringatan SET 
              nomor_surat = ?, mahasiswa_id = ?, jenis_pelanggaran = ?, keterangan = ?, sanksi = ?,
              tanggal_pelanggaran = ?, tanggal_surat = ?, penandatangan = ?, status = ?,
              lampiran_filename = ?, lampiran_path = ?, lampiran_size = ?, updated_at = NOW()
              WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sisssssssssii", 
        $nomor_surat, $mahasiswa_id, $jenis_pelanggaran, $keterangan, $sanksi,
        $tanggal_pelanggaran, $tanggal_surat, $penandatangan, $status,
        $lampiran_filename, $lampiran_path, $lampiran_size, $surat_id
    );
} else {
    $query = "UPDATE surat_peringatan SET 
              nomor_surat = ?, mahasiswa_id = ?, jenis_pelanggaran = ?, keterangan = ?, sanksi = ?,
              tanggal_pelanggaran = ?, tanggal_surat = ?, penandatangan = ?, status = ?,
              lampiran_filename = NULL, lampiran_path = NULL, lampiran_size = NULL, updated_at = NOW()
              WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sisssssssi", 
        $nomor_surat, $mahasiswa_id, $jenis_pelanggaran, $keterangan, $sanksi,
        $tanggal_pelanggaran, $tanggal_surat, $penandatangan, $status, $surat_id
    );
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'Surat berhasil diperbarui']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui surat: ' . mysqli_error($koneksi)]);
}
?>
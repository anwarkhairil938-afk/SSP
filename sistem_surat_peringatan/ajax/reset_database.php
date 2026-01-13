<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Hapus semua data kecuali akun admin yang sedang login
    
    // Hapus file upload
    $upload_dir = "../uploads/";
    if (file_exists($upload_dir)) {
        deleteDirectory($upload_dir);
    }
    
    // Hapus surat peringatan
    mysqli_query($koneksi, "DELETE FROM surat_peringatan");
    
    // Hapus mahasiswa (dan users terkait kecuali admin)
    $mahasiswa_query = "SELECT m.user_id FROM mahasiswa m 
                        JOIN users u ON m.user_id = u.id 
                        WHERE u.id != '$current_user_id'";
    $mahasiswa_result = mysqli_query($koneksi, $mahasiswa_query);
    
    while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
        $user_id = $row['user_id'];
        mysqli_query($koneksi, "DELETE FROM mahasiswa WHERE user_id = '$user_id'");
        mysqli_query($koneksi, "DELETE FROM users WHERE id = '$user_id'");
    }
    
    // Reset sequence/auto increment jika perlu
    mysqli_query($koneksi, "ALTER TABLE surat_peringatan AUTO_INCREMENT = 1");
    mysqli_query($koneksi, "ALTER TABLE mahasiswa AUTO_INCREMENT = 1");
    mysqli_query($koneksi, "ALTER TABLE users AUTO_INCREMENT = 2"); // Mulai dari 2 karena admin sudah ada di id 1
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Sistem berhasil direset']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    
    if (!is_dir($dir)) return unlink($dir);
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}
?>
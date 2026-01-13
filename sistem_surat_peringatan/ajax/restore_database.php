<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$backup_data = json_decode(file_get_contents('php://input'), true);

if (!$backup_data) {
    echo json_encode(['success' => false, 'message' => 'Data backup tidak valid']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Kosongkan tabel terlebih dahulu (kecuali admin yang sedang login)
    $current_user_id = $_SESSION['user_id'];
    
    // Hapus data lama
    mysqli_query($koneksi, "DELETE FROM surat_peringatan");
    mysqli_query($koneksi, "DELETE FROM mahasiswa");
    mysqli_query($koneksi, "DELETE FROM users WHERE id != '$current_user_id'");
    
    // Restore users
    if (isset($backup_data['users'])) {
        foreach ($backup_data['users'] as $user) {
            // Skip user yang sedang login
            if ($user['id'] == $current_user_id) continue;
            
            $username = mysqli_real_escape_string($koneksi, $user['username']);
            $password = mysqli_real_escape_string($koneksi, $user['password']);
            $nama = mysqli_real_escape_string($koneksi, $user['nama']);
            $role = mysqli_real_escape_string($koneksi, $user['role']);
            $status = mysqli_real_escape_string($koneksi, $user['status']);
            
            $query = "INSERT INTO users (username, password, nama, role, status, created_at) 
                      VALUES ('$username', '$password', '$nama', '$role', '$status', NOW())";
            
            if (!mysqli_query($koneksi, $query)) {
                throw new Exception('Gagal restore user: ' . mysqli_error($koneksi));
            }
            
            $new_user_id = mysqli_insert_id($koneksi);
            
            // Simpan mapping ID lama -> baru
            $user_id_map[$user['id']] = $new_user_id;
        }
    }
    
    // Restore mahasiswa
    if (isset($backup_data['mahasiswa'])) {
        foreach ($backup_data['mahasiswa'] as $mahasiswa) {
            $nama = mysqli_real_escape_string($koneksi, $mahasiswa['nama']);
            $nim = mysqli_real_escape_string($koneksi, $mahasiswa['nim']);
            $program_studi = mysqli_real_escape_string($koneksi, $mahasiswa['program_studi']);
            $semester = mysqli_real_escape_string($koneksi, $mahasiswa['semester']);
            $status = mysqli_real_escape_string($koneksi, $mahasiswa['status']);
            
            // Gunakan user_id baru dari mapping
            $old_user_id = $mahasiswa['user_id'];
            $new_user_id = isset($user_id_map[$old_user_id]) ? $user_id_map[$old_user_id] : null;
            
            if ($new_user_id) {
                $query = "INSERT INTO mahasiswa (user_id, nama, nim, program_studi, semester, status, created_at) 
                          VALUES ('$new_user_id', '$nama', '$nim', '$program_studi', '$semester', '$status', NOW())";
                
                if (!mysqli_query($koneksi, $query)) {
                    throw new Exception('Gagal restore mahasiswa: ' . mysqli_error($koneksi));
                }
                
                $new_mahasiswa_id = mysqli_insert_id($koneksi);
                
                // Simpan mapping ID mahasiswa lama -> baru
                $mahasiswa_id_map[$mahasiswa['id']] = $new_mahasiswa_id;
            }
        }
    }
    
    // Restore surat peringatan
    if (isset($backup_data['surat_peringatan'])) {
        foreach ($backup_data['surat_peringatan'] as $surat) {
            $nomor_surat = mysqli_real_escape_string($koneksi, $surat['nomor_surat']);
            $jenis_pelanggaran = mysqli_real_escape_string($koneksi, $surat['jenis_pelanggaran']);
            $keterangan = mysqli_real_escape_string($koneksi, $surat['keterangan']);
            $sanksi = mysqli_real_escape_string($koneksi, $surat['sanksi']);
            $tanggal_surat = mysqli_real_escape_string($koneksi, $surat['tanggal_surat']);
            $tanggal_pelanggaran = mysqli_real_escape_string($koneksi, $surat['tanggal_pelanggaran']);
            $penandatangan = mysqli_real_escape_string($koneksi, $surat['penandatangan']);
            $status = mysqli_real_escape_string($koneksi, $surat['status']);
            
            // Gunakan mahasiswa_id baru dari mapping
            $old_mahasiswa_id = $surat['mahasiswa_id'];
            $new_mahasiswa_id = isset($mahasiswa_id_map[$old_mahasiswa_id]) ? $mahasiswa_id_map[$old_mahasiswa_id] : null;
            
            // Gunakan created_by baru dari mapping
            $old_created_by = $surat['created_by'];
            $new_created_by = isset($user_id_map[$old_created_by]) ? $user_id_map[$old_created_by] : $current_user_id;
            
            if ($new_mahasiswa_id) {
                $query = "INSERT INTO surat_peringatan (nomor_surat, mahasiswa_id, jenis_pelanggaran, keterangan, sanksi, 
                          tanggal_surat, tanggal_pelanggaran, penandatangan, status, created_by, created_at) 
                          VALUES ('$nomor_surat', '$new_mahasiswa_id', '$jenis_pelanggaran', '$keterangan', '$sanksi', 
                          '$tanggal_surat', '$tanggal_pelanggaran', '$penandatangan', '$status', '$new_created_by', NOW())";
                
                if (!mysqli_query($koneksi, $query)) {
                    throw new Exception('Gagal restore surat: ' . mysqli_error($koneksi));
                }
            }
        }
    }
    
    mysqli_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Backup berhasil direstore']);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
-- Database: sistem_surat_peringatan
CREATE DATABASE IF NOT EXISTS sistem_surat_peringatan 
DEFAULT CHARACTER SET utf8mb4 
DEFAULT COLLATE utf8mb4_general_ci;

USE sistem_surat_peringatan;

-- Table: users (untuk semua pengguna)
CREATE TABLE users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: mahasiswa (data khusus mahasiswa)
CREATE TABLE mahasiswa (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) UNIQUE NOT NULL,
    program_studi VARCHAR(100),
    semester INT(2),
    status ENUM('aktif', 'nonaktif', 'cuti') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_nim (nim),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: surat_peringatan
CREATE TABLE surat_peringatan (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nomor_surat VARCHAR(50) UNIQUE NOT NULL,
    mahasiswa_id INT(11) NOT NULL,
    jenis_pelanggaran ENUM('akademik', 'etika', 'administrasi', 'lainnya') NOT NULL,
    keterangan TEXT NOT NULL,
    sanksi TEXT NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_pelanggaran DATE NOT NULL,
    penandatangan VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bukti_gambar VARCHAR(255),
    bukti_dokumen VARCHAR(255),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_nomor_surat (nomor_surat),
    INDEX idx_mahasiswa_id (mahasiswa_id),
    INDEX idx_status (status),
    INDEX idx_jenis_pelanggaran (jenis_pelanggaran),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: settings (untuk pengaturan sistem)
CREATE TABLE settings (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: backup_history (riwayat backup)
CREATE TABLE backup_history (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    size VARCHAR(20),
    created_by INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert data admin default (password: admin123)
INSERT INTO users (username, password, nama, role, status) VALUES 
('admin', 'admin123', 'Administrator Sistem', 'admin', 'aktif');

-- Insert data mahasiswa contoh
INSERT INTO users (username, password, nama, role, status) VALUES 
('mahasiswa1', 'mahasiswa123', 'John Doe', 'mahasiswa', 'aktif'),
('mahasiswa2', 'mahasiswa123', 'Jane Smith', 'mahasiswa', 'aktif');

-- Ambil ID user untuk mahasiswa
SET @user1_id = (SELECT id FROM users WHERE username = 'mahasiswa1');
SET @user2_id = (SELECT id FROM users WHERE username = 'mahasiswa2');

-- Insert data mahasiswa
INSERT INTO mahasiswa (user_id, nama, nim, program_studi, semester, status) VALUES 
(@user1_id, 'John Doe', '2023001', 'Teknik Informatika', 3, 'aktif'),
(@user2_id, 'Jane Smith', '2023002', 'Sistem Informasi', 5, 'aktif');

-- Insert pengaturan default
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('nama_aplikasi', 'Sistem Surat Peringatan Polibatam', 'Nama aplikasi'),
('versi_aplikasi', '1.0.0', 'Versi aplikasi'),
('nama_institusi', 'Politeknik Negeri Batam', 'Nama institusi'),
('alamat_institusi', 'Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461', 'Alamat institusi'),
('telp_institusi', '(0778) 469858', 'Telepon institusi'),
('email_institusi', 'polibatam@polibatam.ac.id', 'Email institusi'),
('logo_path', 'images/POLIBATAM LOGO.png', 'Path logo aplikasi'),
('wallpaper_path', 'images/WALLPAPER POLIBATAM.jpg', 'Path wallpaper login');

-- Insert contoh surat peringatan
SET @mahasiswa1_id = (SELECT id FROM mahasiswa WHERE nim = '2023001');
SET @admin_id = (SELECT id FROM users WHERE username = 'admin');

INSERT INTO surat_peringatan (nomor_surat, mahasiswa_id, jenis_pelanggaran, keterangan, sanksi, 
                              tanggal_surat, tanggal_pelanggaran, penandatangan, status, created_by) VALUES 
('SP/2023/001', @mahasiswa1_id, 'akademik', 'Tidak mengumpulkan tugas 3 kali berturut-turut', 
 'Peringatan tertulis dan wajib konsultasi dengan dosen', '2023-10-01', '2023-09-28', 'Dr. Ahmad', 'approved', @admin_id),
('SP/2023/002', @mahasiswa1_id, 'etika', 'Terlambat masuk kelas tanpa izin', 
 'Peringatan tertulis dan membuat surat pernyataan', '2023-10-05', '2023-10-04', 'Dr. Budi', 'pending', @admin_id);
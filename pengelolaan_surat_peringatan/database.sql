-- Database structure for Sistem Surat Peringatan Polibatam

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'mahasiswa') DEFAULT 'mahasiswa',
    nim VARCHAR(20),
    program_studi VARCHAR(100),
    semester INT,
    status ENUM('aktif', 'nonaktif', 'cuti') DEFAULT 'aktif',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) UNIQUE NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    status ENUM('aktif', 'nonaktif', 'cuti') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: surat_peringatan
CREATE TABLE IF NOT EXISTS surat_peringatan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_surat VARCHAR(50) UNIQUE NOT NULL,
    mahasiswa_id INT NOT NULL,
    jenis_pelanggaran ENUM('akademik', 'etika', 'administrasi', 'lainnya') DEFAULT 'lainnya',
    keterangan TEXT NOT NULL,
    sanksi TEXT NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_pelanggaran DATE NOT NULL,
    penandatangan VARCHAR(100) NOT NULL,
    bukti_gambar VARCHAR(255),
    bukti_dokumen VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table: lampiran_surat (jika diperlukan)
CREATE TABLE IF NOT EXISTS lampiran_surat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    surat_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(50) NOT NULL,
    path_file VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (surat_id) REFERENCES surat_peringatan(id) ON DELETE CASCADE
);

-- View: view_surat_lengkap
CREATE OR REPLACE VIEW view_surat_lengkap AS
SELECT 
    s.*,
    m.nama as nama_mahasiswa,
    m.nim,
    m.program_studi,
    m.semester,
    u.nama as admin_nama
FROM surat_peringatan s
JOIN mahasiswa m ON s.mahasiswa_id = m.id
JOIN users u ON s.created_by = u.id;

-- Procedure: get_mahasiswa_filtered
DELIMITER $$
CREATE PROCEDURE get_mahasiswa_filtered(
    IN p_status VARCHAR(20),
    IN p_program_studi VARCHAR(100),
    IN p_search VARCHAR(100)
)
BEGIN
    SELECT 
        m.*,
        u.username
    FROM mahasiswa m
    JOIN users u ON m.user_id = u.id
    WHERE 
        (p_status = '' OR m.status = p_status)
        AND (p_program_studi = '' OR m.program_studi = p_program_studi)
        AND (p_search = '' OR 
            m.nama LIKE CONCAT('%', p_search, '%') OR
            m.nim LIKE CONCAT('%', p_search, '%') OR
            m.program_studi LIKE CONCAT('%', p_search, '%'))
    ORDER BY m.nama;
END$$
DELIMITER ;

-- Procedure: generate_nomor_surat_procedure
DELIMITER $$
CREATE PROCEDURE generate_nomor_surat_procedure(
    IN p_year INT,
    OUT p_nomor_surat VARCHAR(50)
)
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) + 1 INTO v_count 
    FROM surat_peringatan 
    WHERE YEAR(tanggal_surat) = p_year;
    
    SET p_nomor_surat = CONCAT('SP/', p_year, '/', LPAD(v_count, 3, '0'));
END$$
DELIMITER ;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, nama, role, status) 
VALUES ('admin', 'admin123', 'Administrator', 'admin', 'aktif');
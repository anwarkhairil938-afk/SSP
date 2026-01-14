<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT s.*, m.nama as mahasiswa_nama, m.nim, m.program_studi, m.semester 
          FROM surat_peringatan s 
          JOIN mahasiswa m ON s.mahasiswa_id = m.id 
          WHERE s.id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $jenis_text = getJenisText($row['jenis_pelanggaran']);
    $tanggal_surat = date('d F Y', strtotime($row['tanggal_surat']));
    $tanggal_pelanggaran = date('d F Y', strtotime($row['tanggal_pelanggaran']));
    ?>
    
    <div class="surat-template">
        <div class="text-center mb-4">
            <h4 class="fw-bold">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h4>
            <h3 class="fw-bold text-primary">POLITEKNIK NEGERI BATAM</h3>
            <p>Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461</p>
            <p>Telepon: (0778) 469858 | Email: polibatam@polibatam.ac.id</p>
            <hr class="border-primary border-2">
        </div>
        
        <div class="text-center mb-4">
            <h2 class="fw-bold">SURAT PERINGATAN</h2>
            <h4 class="text-muted">Nomor: <?php echo $row['nomor_surat']; ?></h4>
        </div>
        
        <div class="mb-4">
            <p>Yang bertanda tangan di bawah ini:</p>
            <p style="margin-left: 30px;">
                <strong><?php echo $row['penandatangan']; ?></strong><br>
                <em>Jabatan: Administrator Sistem Surat Peringatan</em><br>
                Politeknik Negeri Batam
            </p>
        </div>
        
        <div class="mb-4">
            <p>Dengan ini memberikan Surat Peringatan kepada:</p>
            <table class="table table-borderless">
                <tr>
                    <td width="150">Nama</td>
                    <td width="20">:</td>
                    <td><strong><?php echo $row['mahasiswa_nama']; ?></strong></td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>:</td>
                    <td><?php echo $row['nim']; ?></td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>:</td>
                    <td><?php echo $row['program_studi']; ?></td>
                </tr>
                <tr>
                    <td>Semester</td>
                    <td>:</td>
                    <td><?php echo $row['semester']; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="mb-4">
            <p><strong>Jenis Pelanggaran:</strong> <?php echo $jenis_text; ?></p>
            <p><strong>Tanggal Pelanggaran:</strong> <?php echo $tanggal_pelanggaran; ?></p>
            
            <p><strong>Keterangan Pelanggaran:</strong></p>
            <div style="margin-left: 20px; margin-bottom: 15px; white-space: pre-line;">
                <?php echo htmlspecialchars($row['keterangan']); ?>
            </div>
            
            <p><strong>Sanksi:</strong></p>
            <div style="margin-left: 20px; white-space: pre-line;">
                <?php echo htmlspecialchars($row['sanksi']); ?>
            </div>
        </div>
        
        <div class="mt-5 pt-5 text-end">
            <div class="d-inline-block text-center">
                <div class="mb-3">Batam, <?php echo $tanggal_surat; ?></div>
                <div style="margin-top: 100px;">
                    <strong><?php echo $row['penandatangan']; ?></strong><br>
                    <em>Administrator Sistem</em>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} else {
    echo '<div class="alert alert-danger">Data surat tidak ditemukan</div>';
}

function getJenisText($jenis) {
    $jenis_map = [
        'akademik' => 'Pelanggaran Akademik',
        'etika' => 'Pelanggaran Etika',
        'administrasi' => 'Pelanggaran Administrasi',
        'lainnya' => 'Pelanggaran Lainnya'
    ];
    return $jenis_map[$jenis] ?? $jenis;
}
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

require_once('../vendor/autoload.php'); // Pastikan sudah install TCPDF via Composer

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    exit('ID tidak valid');
}

$query = "SELECT s.*, m.nama as mahasiswa_nama, m.nim, m.program_studi, m.semester 
          FROM surat_peringatan s 
          JOIN mahasiswa m ON s.mahasiswa_id = m.id 
          WHERE s.id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Sistem Surat Peringatan Polibatam');
    $pdf->SetAuthor('Politeknik Negeri Batam');
    $pdf->SetTitle('Surat Peringatan - ' . $row['nomor_surat']);
    $pdf->SetSubject('Surat Peringatan');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(20, 20, 20);
    
    // Add a page
    $pdf->AddPage();
    
    // Generate HTML content
    $html = generateSuratHTML($row);
    
    // Write HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Output PDF
    $pdf->Output('surat_peringatan_' . $row['nomor_surat'] . '.pdf', 'D');
    
} else {
    echo 'Data tidak ditemukan';
}

function generateSuratHTML($data) {
    $jenis_text = getJenisText($data['jenis_pelanggaran']);
    $tanggal_surat = date('d F Y', strtotime($data['tanggal_surat']));
    $tanggal_pelanggaran = date('d F Y', strtotime($data['tanggal_pelanggaran']));
    
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #2c3e50; font-size: 20px; margin-bottom: 5px; font-weight: bold; }
        .header h2 { color: #34495e; font-size: 16px; margin-bottom: 5px; font-weight: normal; }
        .header p { color: #7f8c8d; font-size: 12px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        .field { margin-bottom: 10px; }
        .field label { font-weight: bold; width: 120px; display: inline-block; }
        .footer { margin-top: 50px; text-align: right; }
        .ttd { margin-top: 80px; }
        .border-top { border-top: 2px solid #2c3e50; padding-top: 10px; }
        .border-bottom { border-bottom: 1px solid #bdc3c7; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
    
    <div class="header">
        <h1>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h1>
        <h2>POLITEKNIK NEGERI BATAM</h2>
        <p>Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461</p>
        <p>Telepon: (0778) 469858 | Email: polibatam@polibatam.ac.id</p>
        <div class="border-top border-bottom"></div>
    </div>
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h3 style="font-weight: bold; color: #2c3e50;">SURAT PERINGATAN</h3>
        <h4 style="color: #7f8c8d;">Nomor: ' . $data['nomor_surat'] . '</h4>
    </div>
    
    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        <p style="margin-left: 30px;">
            <strong>' . $data['penandatangan'] . '</strong><br>
            <em>Jabatan: Administrator Sistem Surat Peringatan</em><br>
            Politeknik Negeri Batam
        </p>
        
        <p>Dengan ini memberikan Surat Peringatan kepada:</p>
        
        <table style="margin-left: 30px; margin-bottom: 20px;">
            <tr>
                <td style="width: 120px;">Nama</td>
                <td style="width: 10px;">:</td>
                <td><strong>' . $data['mahasiswa_nama'] . '</strong></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td>' . $data['nim'] . '</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>' . $data['program_studi'] . '</td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>:</td>
                <td>' . $data['semester'] . '</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <p><strong>Jenis Pelanggaran:</strong> ' . $jenis_text . '</p>
            <p><strong>Tanggal Pelanggaran:</strong> ' . $tanggal_pelanggaran . '</p>
            
            <p><strong>Keterangan Pelanggaran:</strong></p>
            <div style="margin-left: 20px; margin-bottom: 15px;">
                ' . nl2br($data['keterangan']) . '
            </div>
            
            <p><strong>Sanksi:</strong></p>
            <div style="margin-left: 20px;">
                ' . nl2br($data['sanksi']) . '
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="ttd">
            <p>Batam, ' . $tanggal_surat . '</p>
            <p>Hormat kami,</p>
            <br><br><br><br>
            <p><strong>' . $data['penandatangan'] . '</strong></p>
            <p><em>Administrator Sistem</em></p>
        </div>
    </div>
    ';
    
    return $html;
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
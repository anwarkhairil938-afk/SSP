<?php
session_start();
require_once "../auth_koneksi/koneksi.php";
require_once('../vendor/autoload.php'); // TCPDF

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

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
    // Check permission
    if ($_SESSION['role'] !== 'admin') {
        exit('Unauthorized');
    }
    
    // Create temp directory
    $tempDir = '../temp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    
    // Create PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Sistem Surat Peringatan Polibatam');
    $pdf->SetAuthor('Politeknik Negeri Batam');
    $pdf->SetTitle('Surat Peringatan - ' . $row['nomor_surat']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();
    
    // Generate PDF content
    $html = generatePDFContent($row);
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdfContent = $pdf->Output('', 'S');
    
    if ($row['lampiran_path']) {
        // Create ZIP with PDF and attachment
        $zip = new ZipArchive();
        $zipFileName = 'surat_peringatan_' . $row['nomor_surat'] . '.zip';
        $zipPath = $tempDir . $zipFileName;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            // Add PDF
            $pdfFileName = 'surat_peringatan_' . $row['nomor_surat'] . '.pdf';
            $zip->addFromString($pdfFileName, $pdfContent);
            
            // Add attachment
            $attachmentPath = '../uploads/' . $row['lampiran_path'];
            if (file_exists($attachmentPath)) {
                $zip->addFile($attachmentPath, 'lampiran_' . $row['lampiran_filename']);
            }
            
            $zip->close();
            
            // Download ZIP
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            
            // Clean up
            unlink($zipPath);
            exit();
        }
    } else {
        // Download PDF only
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="surat_peringatan_' . $row['nomor_surat'] . '.pdf"');
        echo $pdfContent;
        exit();
    }
}

echo 'Gagal membuat file download';

function generatePDFContent($data) {
    $jenis_text = getJenisText($data['jenis_pelanggaran']);
    $tanggal_surat = date('d F Y', strtotime($data['tanggal_surat']));
    $tanggal_pelanggaran = date('d F Y', strtotime($data['tanggal_pelanggaran']));
    
    $lampiran_html = '';
    if ($data['lampiran_filename']) {
        $lampiran_html = '
        <div style="margin-top: 20px;">
            <p><strong>Lampiran:</strong> ' . $data['lampiran_filename'] . '</p>
            <p><em>File lampiran tersedia di sistem</em></p>
        </div>
        ';
    }
    
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #2c3e50; font-size: 18px; margin-bottom: 5px; font-weight: bold; }
        .header h2 { color: #34495e; font-size: 14px; margin-bottom: 5px; font-weight: normal; }
        .header p { color: #7f8c8d; font-size: 11px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        .field { margin-bottom: 10px; }
        .footer { margin-top: 50px; text-align: right; }
        .ttd { margin-top: 80px; }
    </style>
    
    <div class="header">
        <h1>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h1>
        <h2>POLITEKNIK NEGERI BATAM</h2>
        <p>Jl. Ahmad Yani, Batam Kota, Batam, Kepulauan Riau 29461</p>
        <p>Telepon: (0778) 469858 | Email: polibatam@polibatam.ac.id</p>
        <hr>
    </div>
    
    <div style="text-align: center; margin-bottom: 20px;">
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
        
        <div style="margin-top: 15px;">
            <p><strong>Jenis Pelanggaran:</strong> ' . $jenis_text . '</p>
            <p><strong>Tanggal Pelanggaran:</strong> ' . $tanggal_pelanggaran . '</p>
            
            <p><strong>Keterangan Pelanggaran:</strong></p>
            <div style="margin-left: 20px; margin-bottom: 10px;">
                ' . nl2br($data['keterangan']) . '
            </div>
            
            <p><strong>Sanksi:</strong></p>
            <div style="margin-left: 20px;">
                ' . nl2br($data['sanksi']) . '
            </div>
        </div>
        
        ' . $lampiran_html . '
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
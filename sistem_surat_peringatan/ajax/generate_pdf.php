<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

require_once '../vendor/autoload.php'; // Jika menggunakan Composer untuk TCPDF/dompdf

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$query = "SELECT s.*, m.nama as mahasiswa_nama, m.nim, m.program_studi, m.semester 
          FROM surat_peringatan s 
          JOIN mahasiswa m ON s.mahasiswa_id = m.id 
          WHERE s.id = '$id'";
$result = mysqli_query($koneksi, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // Generate PDF menggunakan TCPDF atau dompdf
    // Ini adalah contoh sederhana, Anda perlu install library PDF terlebih dahulu
    
    // Contoh sederhana dengan HTML
    $html = generateSuratHTML($row);
    
    // Untuk implementasi real, gunakan library PDF seperti TCPDF atau dompdf
    // Contoh dengan TCPDF:
    /*
    require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('surat_peringatan_' . $row['nomor_surat'] . '.pdf', 'D');
    */
    
    // Untuk sementara, tampilkan HTML
    echo $html;
} else {
    echo 'Data tidak ditemukan';
}

function generateSuratHTML($data) {
    $jenis_text = getJenisText($data['jenis_pelanggaran']);
    $tanggal_surat = date('d F Y', strtotime($data['tanggal_surat']));
    $tanggal_pelanggaran = date('d F Y', strtotime($data['tanggal_pelanggaran']));
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .header { text-align: center; margin-bottom: 40px; }
            .header h1 { color: #333; margin-bottom: 5px; }
            .header h2 { color: #666; font-size: 18px; margin-bottom: 5px; }
            .content { margin: 30px 0; }
            .field { margin-bottom: 10px; }
            .field label { font-weight: bold; width: 150px; display: inline-block; }
            .footer { margin-top: 50px; text-align: right; }
            .ttd { margin-top: 100px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>SURAT PERINGATAN</h1>
            <h2>Politeknik Negeri Batam</h2>
            <p>Sistem Surat Peringatan Polibatam</p>
        </div>
        
        <div class='content'>
            <div class='field'>
                <label>Nomor Surat:</label>
                <span>{$data['nomor_surat']}</span>
            </div>
            <div class='field'>
                <label>Tanggal:</label>
                <span>{$tanggal_surat}</span>
            </div>
            
            <p>Yang bertanda tangan di bawah ini:</p>
            <p><strong>{$data['penandatangan']}</strong></p>
            <p>Dengan ini memberikan Surat Peringatan kepada:</p>
            
            <div class='field'>
                <label>Nama:</label>
                <span>{$data['mahasiswa_nama']}</span>
            </div>
            <div class='field'>
                <label>NIM:</label>
                <span>{$data['nim']}</span>
            </div>
            <div class='field'>
                <label>Program Studi:</label>
                <span>{$data['program_studi']}</span>
            </div>
            <div class='field'>
                <label>Semester:</label>
                <span>{$data['semester']}</span>
            </div>
            
            <p><strong>Jenis Pelanggaran:</strong> {$jenis_text}</p>
            <p><strong>Keterangan:</strong></p>
            <p>" . nl2br($data['keterangan']) . "</p>
            <p><strong>Sanksi:</strong></p>
            <p>" . nl2br($data['sanksi']) . "</p>
            
            <p>Surat peringatan ini diberikan atas pelanggaran yang terjadi pada tanggal: <strong>{$tanggal_pelanggaran}</strong></p>
        </div>
        
        <div class='footer'>
            <div class='ttd'>
                <p>Hormat kami,</p>
                <br><br><br>
                <p><strong>{$data['penandatangan']}</strong></p>
            </div>
        </div>
    </body>
    </html>";
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
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="no-data">Unauthorized</div>';
    exit();
}

$nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : '';
$nim = isset($_GET['nim']) ? mysqli_real_escape_string($koneksi, $_GET['nim']) : '';
$jenis = isset($_GET['jenis']) ? mysqli_real_escape_string($koneksi, $_GET['jenis']) : '';

$query = "SELECT s.*, m.nama as nama_mahasiswa, m.nim, m.program_studi
          FROM surat_peringatan s
          JOIN mahasiswa m ON s.mahasiswa_id = m.id
          WHERE s.status = 'approved'";

if (!empty($nama)) {
    $query .= " AND m.nama LIKE '%$nama%'";
}

if (!empty($nim)) {
    $query .= " AND m.nim LIKE '%$nim%'";
}

if (!empty($jenis)) {
    $query .= " AND s.jenis_pelanggaran = '$jenis'";
}

$query .= " ORDER BY s.created_at DESC";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    echo '<div class="table-container" style="overflow-x: auto;">';
    echo '<table class="arsip-table">';
    echo '<thead>
            <tr>
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Mahasiswa</th>
                <th>Jenis</th>
                <th>Tanggal Surat</th>
                <th>Tanggal Pelanggaran</th>
                <th>Aksi</th>
            </tr>
          </thead>';
    echo '<tbody>';
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $jenis_text = getJenisText($row['jenis_pelanggaran']);
        
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['nomor_surat']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_mahasiswa']) . '<br><small>' . $row['nim'] . '</small></td>';
        echo '<td>' . $jenis_text . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($row['tanggal_surat'])) . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($row['tanggal_pelanggaran'])) . '</td>';
        echo '<td>
                <button class="btn-action view" onclick="tampilkanSurat(' . $row['id'] . ')" title="Lihat"><i class="fas fa-eye"></i></button>
                <button class="btn-action download" onclick="window.open(\'ajax/generate_pdf.php?id=' . $row['id'] . '\', \'_blank\')" title="Download PDF"><i class="fas fa-download"></i></button>
              </td>';
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
} else {
    echo '<div class="no-data">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>Tidak ada data ditemukan</p>
          </div>';
}

function getJenisText($jenis) {
    $jenis_map = [
        'akademik' => 'Akademik',
        'etika' => 'Etika',
        'administrasi' => 'Administrasi',
        'lainnya' => 'Lainnya'
    ];
    return $jenis_map[$jenis] ?? $jenis;
}
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="no-data">Unauthorized</div>';
    exit();
}

$query = "SELECT s.*, m.nama as nama_mahasiswa, m.nim, m.program_studi
          FROM surat_peringatan s
          JOIN mahasiswa m ON s.mahasiswa_id = m.id
          ORDER BY s.created_at DESC";
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
                <th>Status</th>
                <th>Aksi</th>
            </tr>
          </thead>';
    echo '<tbody>';
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $jenis_text = getJenisText($row['jenis_pelanggaran']);
        $status_class = getStatusClass($row['status']);
        $status_text = getStatusText($row['status']);
        
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['nomor_surat']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_mahasiswa']) . '<br><small>' . $row['nim'] . '</small></td>';
        echo '<td>' . $jenis_text . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($row['tanggal_surat'])) . '</td>';
        echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td>
                <button class="btn-action view" onclick="tampilkanSurat(' . $row['id'] . ')" title="Lihat"><i class="fas fa-eye"></i></button>
                <button class="btn-action edit" onclick="editSurat(' . $row['id'] . ')" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn-action delete" onclick="hapusSurat(' . $row['id'] . ')" title="Hapus"><i class="fas fa-trash"></i></button>
              </td>';
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
} else {
    echo '<div class="no-data">
            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>Belum ada riwayat surat peringatan</p>
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

function getStatusClass($status) {
    $class_map = [
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        'pending' => 'status-pending'
    ];
    return $class_map[$status] ?? 'status-pending';
}

function getStatusText($status) {
    $text_map = [
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'pending' => 'Menunggu'
    ];
    return $text_map[$status] ?? 'Menunggu';
}
?>
<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    echo '<div class="no-data">Unauthorized</div>';
    exit();
}

$query = "SELECT m.*, u.username, u.status as user_status 
          FROM mahasiswa m 
          JOIN users u ON m.user_id = u.id 
          ORDER BY m.nama";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    echo '<div class="table-container" style="overflow-x: auto;">';
    echo '<table class="arsip-table">';
    echo '<thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIM</th>
                <th>Program Studi</th>
                <th>Semester</th>
                <th>Username</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
          </thead>';
    echo '<tbody>';
    
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $status_class = ($row['status'] == 'aktif') ? 'status-approved' : 'status-rejected';
        $status_text = ucfirst($row['status']);
        
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
        echo '<td>' . $row['nim'] . '</td>';
        echo '<td>' . $row['program_studi'] . '</td>';
        echo '<td>' . $row['semester'] . '</td>';
        echo '<td>' . $row['username'] . '</td>';
        echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td>
                <button class="btn-action edit" onclick="editMahasiswa(' . $row['id'] . ')" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn-action delete" onclick="hapusMahasiswa(' . $row['id'] . ')" title="Hapus"><i class="fas fa-trash"></i></button>
              </td>';
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
} else {
    echo '<div class="no-data">
            <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>Belum ada data mahasiswa</p>
          </div>';
}
?>
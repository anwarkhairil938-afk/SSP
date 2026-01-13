<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    echo '<tr><td colspan="6" class="loading">Unauthorized</td></tr>';
    exit();
}

// Tidak tampilkan akun admin yang sedang login
$current_user_id = $_SESSION['user_id'];

$query = "SELECT u.*, m.nim 
          FROM users u 
          LEFT JOIN mahasiswa m ON u.id = m.user_id 
          WHERE u.id != '$current_user_id'
          ORDER BY u.role, u.nama";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $role_text = ($row['role'] == 'admin') ? 'Administrator' : 'Mahasiswa';
        $status_class = ($row['status'] == 'aktif') ? 'status-approved' : 'status-rejected';
        $status_text = ucfirst($row['status']);
        $last_login = $row['last_login'] ? date('d/m/Y H:i', strtotime($row['last_login'])) : '-';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
        echo '<td>' . $role_text . '</td>';
        echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
        echo '<td>' . $last_login . '</td>';
        echo '<td>
                <button class="btn-action edit" onclick="editAkun(' . $row['id'] . ')" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn-action delete" onclick="hapusAkun(' . $row['id'] . ')" title="Hapus"><i class="fas fa-trash"></i></button>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align: center; padding: 40px;">Tidak ada data akun</td></tr>';
}
?>
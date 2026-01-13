<?php
session_start();
require_once "../auth_koneksi/koneksi.php";

if ($_SESSION['role'] !== 'admin') {
    echo '';
    exit();
}

$query = "SELECT * FROM mahasiswa WHERE status = 'aktif' ORDER BY nama";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<option value='{$row['id']}'>{$row['nama']} ({$row['nim']})</option>";
}
?>
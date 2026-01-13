<?php
require_once "auth_koneksi/koneksi.php";

// Data admin
$username = "admin";
$password = "admin123"; // Plain text untuk testing
$nama = "Administrator Sistem";
$role = "admin";

// Cek apakah admin sudah ada
$check = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
if (mysqli_num_rows($check) == 0) {
    $insert = "INSERT INTO users (username, password, nama, role, status) 
               VALUES ('$username', '$password', '$nama', '$role', 'aktif')";
    
    if (mysqli_query($koneksi, $insert)) {
        echo "User admin berhasil dibuat!<br>";
        echo "Login dengan:<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Role: Administrator<br>";
        echo '<a href="login.php">Klik di sini untuk login</a>';
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
} else {
    echo "User admin sudah ada di database.<br>";
    echo "Login dengan:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "Role: Administrator<br>";
    echo '<a href="login.php">Klik di sini untuk login</a>';
}
?>
<?php
// auth_koneksi/auth.php

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk memeriksa role user
function checkRole($requiredRole) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $requiredRole;
}

// Fungsi untuk mendapatkan user data
function getUserData($koneksi, $user_id) {
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result);
}
?>
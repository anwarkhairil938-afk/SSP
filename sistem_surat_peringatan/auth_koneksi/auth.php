<?php
// Redirect ke login jika belum login
function require_login() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect berdasarkan role
function redirect_based_on_role() {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: dashboard_admin.php");
        } elseif ($_SESSION['role'] === 'mahasiswa') {
            header("Location: dashboard_mahasiswa.php");
        }
        exit();
    }
}

// Cek role admin
function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: unauthorized.php");
        exit();
    }
}

// Cek role mahasiswa
function require_mahasiswa() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
        header("Location: unauthorized.php");
        exit();
    }
}

// Logout function
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
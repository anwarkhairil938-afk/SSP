<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_mahasiswa.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
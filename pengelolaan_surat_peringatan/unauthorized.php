<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Sistem Surat Peringatan Polibatam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .error-card {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="error-card">
                    <div class="error-icon">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <h2>Akses Ditolak</h2>
                    <p class="text-muted mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                    
                    <div class="d-grid gap-2">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="landing_page.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
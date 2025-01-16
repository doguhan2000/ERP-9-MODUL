<?php
session_start();

// Eğer zaten giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION['finance_logged_in']) && $_SESSION['finance_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Giriş kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === '1453') {
        $_SESSION['finance_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Hatalı şifre!';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finans Yönetimi Giriş - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">Finans Yönetimi</h4>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="../../index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Ana Menüye Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
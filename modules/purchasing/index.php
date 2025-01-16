<?php
require_once '../../config/db.php';

// Ana kategorileri getir
$categories = $conn->query("
    SELECT * FROM inventory_categories 
    WHERE status = 'active'
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alma Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Satın Alma Yönetimi</h2>
                    <a href="suppliers.php" class="btn btn-primary">
                        <i class="bi bi-building"></i> Tedarikçi Yönetimi
                    </a>
                </div>
                
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($category['description']) ?></p>
                                <a href="category_products.php?id=<?= $category['id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Ürünleri Görüntüle
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
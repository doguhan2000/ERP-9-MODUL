<?php
require_once '../../config/db.php';

// Kategorileri ve stok bilgilerini getir
$categories = $conn->query("
    SELECT 
        c.*,
        COUNT(i.id) as total_items,
        SUM(CASE WHEN i.quantity > 0 THEN 1 ELSE 0 END) as available_items
    FROM inventory_categories c
    LEFT JOIN inventory_items i ON c.id = i.category_id
    WHERE c.status = 'active' AND i.status = 'active'
    GROUP BY c.id
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Yönetimi - 9ERP</title>
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
                    <h2>Satış Yönetimi</h2>
                    <div>
                        <a href="customers.php" class="btn btn-primary me-2">
                            <i class="bi bi-people"></i> Müşteriler
                        </a>
                        <a href="reports.php" class="btn btn-secondary">
                            <i class="bi bi-file-text"></i> Satış Raporları
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($category['description']) ?></p>
                                <div class="d-flex justify-content-between mb-3">
                                    <small class="text-muted">Toplam Ürün: <?= $category['total_items'] ?></small>
                                    <small class="text-muted">Satışta: <?= $category['available_items'] ?></small>
                                </div>
                                <a href="customers.php?category=<?= $category['id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-cart"></i> Satış Başlat
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
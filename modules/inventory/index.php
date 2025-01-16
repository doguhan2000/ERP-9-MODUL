<?php
require_once '../../config/db.php';

// Kategorileri getir
$categories = $conn->query("
    SELECT 
        c.*,
        COUNT(i.id) as total_items,
        SUM(i.quantity) as total_stock
    FROM inventory_categories c
    LEFT JOIN inventory_items i ON c.id = i.category_id
    WHERE c.status = 'active'
    GROUP BY c.id
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Yönetimi - 9ERP</title>
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
                    <h2>Stok Yönetimi</h2>
                    <button class="btn btn-primary" onclick="location.href='new_item.php'">
                        <i class="bi bi-plus-circle"></i> Yeni Ürün Ekle
                    </button>
                </div>

                <!-- Kategoriler -->
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title"><?= htmlspecialchars($category['name']) ?></h5>
                                        <span class="badge bg-primary"><?= $category['total_items'] ?> Ürün</span>
                                    </div>
                                    <p class="card-text text-muted"><?= htmlspecialchars($category['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Toplam Stok: <?= $category['total_stock'] ?? 0 ?></span>
                                        <a href="category_items.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            Ürünleri Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Kritik Stok Uyarıları -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kritik Stok Uyarıları</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $low_stock = $conn->query("
                            SELECT i.*, c.name as category_name
                            FROM inventory_items i
                            JOIN inventory_categories c ON i.category_id = c.id
                            WHERE i.quantity <= i.min_quantity
                            AND i.status = 'active'
                        ")->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (count($low_stock) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ürün Kodu</th>
                                            <th>Ürün Adı</th>
                                            <th>Kategori</th>
                                            <th>Mevcut Stok</th>
                                            <th>Min. Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['item_code']) ?></td>
                                                <td><?= htmlspecialchars($item['name']) ?></td>
                                                <td><?= htmlspecialchars($item['category_name']) ?></td>
                                                <td class="text-danger"><?= $item['quantity'] ?></td>
                                                <td><?= $item['min_quantity'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Kritik stok seviyesinde ürün bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
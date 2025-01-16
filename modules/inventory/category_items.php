<?php
require_once '../../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = $_GET['id'];

// Kategori bilgilerini getir
$category = $conn->query("
    SELECT * FROM inventory_categories 
    WHERE id = " . intval($category_id))->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: index.php');
    exit;
}

// Kategoriye ait ürünleri getir
$items = $conn->query("
    SELECT * FROM inventory_items 
    WHERE category_id = " . intval($category_id) . "
    AND status = 'active'
    ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> - Stok Yönetimi</title>
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
                    <div>
                        <h2><?= htmlspecialchars($category['name']) ?></h2>
                        <p class="text-muted"><?= htmlspecialchars($category['description']) ?></p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Geri Dön
                        </a>
                        <button class="btn btn-primary" onclick="location.href='new_item.php?category_id=<?= $category_id ?>'">
                            <i class="bi bi-plus-circle"></i> Yeni Ürün Ekle
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if (count($items) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ürün Kodu</th>
                                            <th>Ürün Adı</th>
                                            <th>Versiyon</th>
                                            <th>Lisans Tipi</th>
                                            <th>Stok</th>
                                            <th>Alış Fiyatı</th>
                                            <th>Satış Fiyatı</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['item_code']) ?></td>
                                                <td><?= htmlspecialchars($item['name']) ?></td>
                                                <td><?= htmlspecialchars($item['version']) ?></td>
                                                <td><?= htmlspecialchars($item['license_type']) ?></td>
                                                <td class="<?= $item['quantity'] <= $item['min_quantity'] ? 'text-danger' : '' ?>">
                                                    <?= $item['quantity'] ?>
                                                </td>
                                                <td><?= number_format($item['purchase_price'], 2) ?> ₺</td>
                                                <td><?= number_format($item['sale_price'], 2) ?> ₺</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editItem(<?= $item['id'] ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Bu kategoride henüz ürün bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editItem(id) {
        location.href = 'edit_item.php?id=' + id;
    }

    function deleteItem(id) {
        if (confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
            fetch('api/delete_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün başarıyla silindi.');
                    location.reload();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
        }
    }
    </script>
</body>
</html> 
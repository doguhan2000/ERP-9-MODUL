<?php
require_once '../../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = intval($_GET['id']);

// Kategori bilgilerini getir
$stmt = $conn->prepare("SELECT * FROM inventory_categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: index.php');
    exit;
}

// Kategorideki ürünleri getir
$stmt = $conn->prepare("
    SELECT 
        i.*,
        sp.id as supplier_product_id,
        sp.price,
        sp.currency,
        sp.delivery_time,
        s.name as supplier_name,
        s.contact_person,
        s.phone,
        s.email
    FROM inventory_items i
    LEFT JOIN supplier_products sp ON i.id = sp.inventory_item_id
    LEFT JOIN suppliers s ON sp.supplier_id = s.id
    WHERE i.category_id = ? AND i.status = 'active'
    ORDER BY i.name ASC, sp.price ASC
");
$stmt->execute([$category_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ürünleri grupla
$grouped_products = [];
foreach ($products as $product) {
    if (!isset($grouped_products[$product['id']])) {
        $grouped_products[$product['id']] = [
            'item' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'item_code' => $product['item_code'],
                'description' => $product['description'],
                'version' => $product['version'],
                'license_type' => $product['license_type']
            ],
            'suppliers' => []
        ];
    }
    if ($product['supplier_product_id']) {
        $grouped_products[$product['id']]['suppliers'][] = [
            'id' => $product['supplier_product_id'],
            'name' => $product['supplier_name'],
            'price' => $product['price'],
            'currency' => $product['currency'],
            'delivery_time' => $product['delivery_time'],
            'contact_person' => $product['contact_person'],
            'phone' => $product['phone'],
            'email' => $product['email']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> - Satın Alma</title>
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
                    <h2><?= htmlspecialchars($category['name']) ?></h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Geri
                    </a>
                </div>

                <?php foreach ($grouped_products as $product): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?= htmlspecialchars($product['item']['name']) ?> 
                            <small class="text-muted">(<?= htmlspecialchars($product['item']['item_code']) ?>)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Versiyon:</strong> <?= htmlspecialchars($product['item']['version']) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Lisans Tipi:</strong> <?= htmlspecialchars($product['item']['license_type']) ?>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Tedarikçi Teklifleri</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tedarikçi</th>
                                        <th>Fiyat</th>
                                        <th>Teslimat Süresi</th>
                                        <th>İletişim</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product['suppliers'] as $supplier): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                                        <td><?= number_format($supplier['price'], 2) ?> <?= $supplier['currency'] ?></td>
                                        <td><?= $supplier['delivery_time'] ?> gün</td>
                                        <td>
                                            <?= htmlspecialchars($supplier['contact_person']) ?><br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($supplier['phone']) ?><br>
                                                <?= htmlspecialchars($supplier['email']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick="purchaseProduct(<?= $product['item']['id'] ?>, <?= $supplier['id'] ?>)">
                                                <i class="bi bi-cart-plus"></i> Satın Al
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function purchaseProduct(itemId, supplierProductId) {
        if (confirm('Bu ürünü satın almak istediğinizden emin misiniz?')) {
            fetch('api/purchase_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&supplier_product_id=${supplierProductId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün başarıyla satın alındı ve stoka eklendi.');
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
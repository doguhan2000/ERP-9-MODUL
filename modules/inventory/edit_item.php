<?php
require_once '../../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$item_id = intval($_GET['id']);

// Ürün bilgilerini getir
$stmt = $conn->prepare("SELECT * FROM inventory_items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: index.php');
    exit;
}

// Kategorileri getir
$categories = $conn->query("SELECT * FROM inventory_categories WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

// POST işlemi kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE inventory_items SET 
                category_id = ?,
                item_code = ?,
                name = ?,
                description = ?,
                version = ?,
                license_type = ?,
                purchase_price = ?,
                sale_price = ?,
                quantity = ?,
                min_quantity = ?,
                supplier_info = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['category_id'],
            $_POST['item_code'],
            $_POST['name'],
            $_POST['description'],
            $_POST['version'],
            $_POST['license_type'],
            $_POST['purchase_price'],
            $_POST['sale_price'],
            $_POST['quantity'],
            $_POST['min_quantity'],
            $_POST['supplier_info'],
            $item_id
        ]);

        header('Location: category_items.php?id=' . $_POST['category_id'] . '&success=1');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenle - Stok Yönetimi</title>
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
                    <h2>Ürün Düzenle</h2>
                    <a href="category_items.php?id=<?= $item['category_id'] ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Geri Dön
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select name="category_id" class="form-select" required>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" <?= $category['id'] == $item['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ürün Kodu</label>
                                    <input type="text" name="item_code" class="form-control" value="<?= htmlspecialchars($item['item_code']) ?>" required>
                                </div>

                                <!-- Diğer form alanları -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ürün Adı</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Versiyon</label>
                                    <input type="text" name="version" class="form-control" value="<?= htmlspecialchars($item['version']) ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lisans Tipi</label>
                                    <select name="license_type" class="form-select" required>
                                        <option value="perpetual" <?= $item['license_type'] == 'perpetual' ? 'selected' : '' ?>>Süresiz</option>
                                        <option value="subscription" <?= $item['license_type'] == 'subscription' ? 'selected' : '' ?>>Abonelik</option>
                                        <option value="opensource" <?= $item['license_type'] == 'opensource' ? 'selected' : '' ?>>Açık Kaynak</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alış Fiyatı</label>
                                    <input type="number" name="purchase_price" class="form-control" step="0.01" value="<?= $item['purchase_price'] ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Satış Fiyatı</label>
                                    <input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $item['sale_price'] ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stok Miktarı</label>
                                    <input type="number" name="quantity" class="form-control" value="<?= $item['quantity'] ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Stok</label>
                                    <input type="number" name="min_quantity" class="form-control" value="<?= $item['min_quantity'] ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tedarikçi Bilgisi</label>
                                    <input type="text" name="supplier_info" class="form-control" value="<?= htmlspecialchars($item['supplier_info']) ?>">
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Açıklama</label>
                                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
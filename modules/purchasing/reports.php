<?php
require_once '../../config/db.php';

// Son 30 günlük satın alma işlemlerini getir
$stmt = $conn->query("
    SELECT 
        pt.*,
        i.name as item_name,
        i.item_code,
        s.name as supplier_name,
        ic.name as category_name
    FROM purchase_transactions pt
    JOIN inventory_items i ON pt.inventory_item_id = i.id
    JOIN suppliers s ON pt.supplier_id = s.id
    JOIN inventory_categories ic ON i.category_id = ic.id
    ORDER BY pt.purchase_date DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alma Raporları - 9ERP</title>
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
                <h2 class="mb-4">Satın Alma Raporları</h2>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Kategori</th>
                                        <th>Ürün Kodu</th>
                                        <th>Ürün Adı</th>
                                        <th>Tedarikçi</th>
                                        <th>Miktar</th>
                                        <th>Fiyat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($transaction['purchase_date'])) ?></td>
                                        <td><?= htmlspecialchars($transaction['category_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['item_code']) ?></td>
                                        <td><?= htmlspecialchars($transaction['item_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['supplier_name']) ?></td>
                                        <td><?= $transaction['quantity'] ?></td>
                                        <td><?= number_format($transaction['price'], 2) ?> <?= $transaction['currency'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
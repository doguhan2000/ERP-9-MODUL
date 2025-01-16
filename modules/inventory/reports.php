<?php
require_once '../../config/db.php';

// Stok hareketlerini getir
$stmt = $conn->query("
    SELECT 
        pt.purchase_date as transaction_date,
        'Satın Alma' as transaction_type,
        i.item_code,
        i.name as item_name,
        ic.name as category_name,
        s.name as supplier_name,
        pt.quantity,
        pt.price,
        pt.currency,
        i.quantity as current_stock
    FROM purchase_transactions pt
    JOIN inventory_items i ON pt.inventory_item_id = i.id
    JOIN inventory_categories ic ON i.category_id = ic.id
    JOIN suppliers s ON pt.supplier_id = s.id
    ORDER BY pt.purchase_date DESC
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Raporları - 9ERP</title>
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
                <h2 class="mb-4">Stok Raporları</h2>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem Türü</th>
                                        <th>Kategori</th>
                                        <th>Ürün Kodu</th>
                                        <th>Ürün Adı</th>
                                        <th>Tedarikçi</th>
                                        <th>Miktar</th>
                                        <th>Birim Fiyat</th>
                                        <th>Mevcut Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($transaction['transaction_date'])) ?></td>
                                        <td><?= htmlspecialchars($transaction['transaction_type']) ?></td>
                                        <td><?= htmlspecialchars($transaction['category_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['item_code']) ?></td>
                                        <td><?= htmlspecialchars($transaction['item_name']) ?></td>
                                        <td><?= htmlspecialchars($transaction['supplier_name']) ?></td>
                                        <td><?= $transaction['quantity'] ?></td>
                                        <td><?= number_format($transaction['price'], 2) ?> <?= $transaction['currency'] ?></td>
                                        <td><?= $transaction['current_stock'] ?></td>
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
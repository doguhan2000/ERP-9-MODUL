<?php
require_once '../../config/db.php';

// Satış raporlarını getir
$stmt = $conn->query("
    SELECT 
        s.id as sale_id,
        s.created_at as sale_date,
        c.name as customer_name,
        c.company_name,
        cg.name as group_name,
        s.total_amount,
        s.discount_rate,
        s.final_amount,
        SUM(si.quantity) as total_items,
        SUM(si.quantity * i.purchase_price) as total_cost,
        (s.final_amount - SUM(si.quantity * i.purchase_price)) as profit,
        ((s.final_amount - SUM(si.quantity * i.purchase_price)) / SUM(si.quantity * i.purchase_price) * 100) as profit_margin
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    LEFT JOIN customer_groups cg ON c.group_id = cg.id
    JOIN sale_items si ON s.id = si.sale_id
    JOIN inventory_items i ON si.inventory_item_id = i.id
    WHERE s.status = 'completed'
    GROUP BY s.id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Raporları - 9ERP</title>
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
                    <h2>Satış Raporları</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Müşteri</th>
                                        <th>Grup</th>
                                        <th>Toplam Tutar</th>
                                        <th>İndirim</th>
                                        <th>Net Tutar</th>
                                        <th>Maliyet</th>
                                        <th>Kâr</th>
                                        <th>Kâr Marjı</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stmt as $sale): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($sale['sale_date'])) ?></td>
                                        <td>
                                            <?= htmlspecialchars($sale['customer_name']) ?>
                                            <?php if ($sale['company_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($sale['company_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($sale['group_name']) ?></td>
                                        <td><?= number_format($sale['total_amount'], 2) ?> TL</td>
                                        <td><?= number_format($sale['discount_rate'], 2) ?>%</td>
                                        <td><?= number_format($sale['final_amount'], 2) ?> TL</td>
                                        <td><?= number_format($sale['total_cost'], 2) ?> TL</td>
                                        <td class="<?= $sale['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($sale['profit'], 2) ?> TL
                                        </td>
                                        <td class="<?= $sale['profit_margin'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            %<?= number_format($sale['profit_margin'], 2) ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewSaleDetails(<?= $sale['sale_id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
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

    <!-- Satış Detay Modal -->
    <div class="modal fade" id="saleDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Satış Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="saleDetailsContent">
                    <!-- Detaylar AJAX ile yüklenecek -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewSaleDetails(saleId) {
        fetch(`api/get_sale_details.php?id=${saleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = `
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ürün Kodu</th>
                                        <th>Ürün Adı</th>
                                        <th>Miktar</th>
                                        <th>Birim Fiyat</th>
                                        <th>Toplam</th>
                                        <th>Maliyet</th>
                                        <th>Kâr</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.items.forEach(item => {
                        const profit = (item.total_price - (item.quantity * item.purchase_price));
                        html += `
                            <tr>
                                <td>${item.item_code}</td>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit_price.toFixed(2)} TL</td>
                                <td>${item.total_price.toFixed(2)} TL</td>
                                <td>${(item.quantity * item.purchase_price).toFixed(2)} TL</td>
                                <td class="${profit >= 0 ? 'text-success' : 'text-danger'}">
                                    ${profit.toFixed(2)} TL
                                </td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table></div>';
                    document.getElementById('saleDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('saleDetailsModal')).show();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
    }
    </script>
</body>
</html> 
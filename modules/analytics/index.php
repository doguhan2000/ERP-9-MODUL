<?php
require_once '../../config/db.php';

// Satış İstatistikleri
$sales_stats = $conn->query("
    SELECT 
        COUNT(*) as total_sales,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_sale,
        COUNT(DISTINCT customer_id) as unique_customers
    FROM sales 
    WHERE status = 'completed'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch(PDO::FETCH_ASSOC);

// Ürün Kategorisi Bazında Satışlar
$category_sales = $conn->query("
    SELECT 
        ic.name as category,
        COUNT(si.id) as total_sales,
        SUM(si.total_price) as revenue
    FROM sale_items si
    JOIN inventory_items ii ON si.inventory_item_id = ii.id
    JOIN inventory_categories ic ON ii.category_id = ic.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.status = 'completed'
    GROUP BY ic.id
    ORDER BY revenue DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Müşteri Aktiviteleri
$customer_activities = $conn->query("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks
    FROM tasks
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İş Zekası ve Raporlama - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">İş Zekası ve Raporlama</h2>

                <!-- Özet Kartları -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Toplam Satış</h6>
                                <h3><?= number_format($sales_stats['total_sales']) ?></h3>
                                <small>Son 30 gün</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Toplam Gelir</h6>
                                <h3><?= number_format($sales_stats['total_revenue'], 2) ?> ₺</h3>
                                <small>Son 30 gün</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Ortalama Satış</h6>
                                <h3><?= number_format($sales_stats['avg_sale'], 2) ?> ₺</h3>
                                <small>Son 30 gün</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">Aktif Müşteriler</h6>
                                <h3><?= number_format($sales_stats['unique_customers']) ?></h3>
                                <small>Son 30 gün</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafikler -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Kategori Bazında Satışlar</h5>
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Müşteri Aktiviteleri</h5>
                                <canvas id="customerActivitiesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kategori Satışları Grafiği
        new Chart(document.getElementById('categorySalesChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($category_sales, 'category')) ?>,
                datasets: [{
                    label: 'Gelir (₺)',
                    data: <?= json_encode(array_column($category_sales, 'revenue')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Müşteri Aktiviteleri Grafiği
        new Chart(document.getElementById('customerActivitiesChart'), {
            type: 'pie',
            data: {
                labels: ['Tamamlanan Görevler', 'Bekleyen Görevler'],
                datasets: [{
                    data: [
                        <?= $customer_activities['completed_tasks'] ?>,
                        <?= $customer_activities['pending_tasks'] ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html> 
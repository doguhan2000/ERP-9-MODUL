<?php
require_once '../../config/db.php';

// Şikayet Analizi
$complaint_analysis = $conn->query("
    SELECT 
        COUNT(*) as total_complaints,
        COUNT(DISTINCT customer_id) as unique_customers,
        AVG(DATEDIFF(updated_at, created_at)) as avg_resolution_time
    FROM notes 
    WHERE type = 'complaint' 
    AND status = 'active'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetch(PDO::FETCH_ASSOC);

// Müşteri Etkileşimleri
$interaction_stats = $conn->query("
    SELECT 
        interaction_type,
        COUNT(*) as total_count
    FROM customer_interactions
    WHERE interaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY interaction_type
")->fetchAll(PDO::FETCH_ASSOC);

// Müşteri Grupları Analizi
$customer_groups = $conn->query("
    SELECT 
        cg.name as group_name,
        COUNT(c.id) as customer_count,
        COUNT(DISTINCT s.id) as total_sales,
        SUM(s.final_amount) as total_revenue
    FROM customer_groups cg
    LEFT JOIN customers c ON cg.id = c.group_id
    LEFT JOIN sales s ON c.id = s.customer_id AND s.status = 'completed'
    WHERE cg.status = 'active'
    GROUP BY cg.id
")->fetchAll(PDO::FETCH_ASSOC);

// En Aktif Müşteriler
$active_customers = $conn->query("
    SELECT 
        c.name,
        c.company_name,
        COUNT(DISTINCT s.id) as total_sales,
        COUNT(DISTINCT n.id) as total_notes,
        COUNT(DISTINCT t.id) as total_tasks,
        SUM(s.final_amount) as total_revenue
    FROM customers c
    LEFT JOIN sales s ON c.id = s.customer_id AND s.status = 'completed'
    LEFT JOIN notes n ON c.id = n.customer_id AND n.status = 'active'
    LEFT JOIN tasks t ON c.id = t.customer_id AND t.status != 'deleted'
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY total_sales DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Raporları - 9ERP</title>
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
                <h2 class="mb-4">Müşteri Raporları</h2>

                <!-- Özet Kartları -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Toplam Şikayet</h5>
                                <h2><?= $complaint_analysis['total_complaints'] ?></h2>
                                <p class="mb-0">
                                    Ortalama Çözüm Süresi: 
                                    <?= round($complaint_analysis['avg_resolution_time']) ?> gün
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Müşteri Etkileşimleri</h5>
                                <h2><?= array_sum(array_column($interaction_stats, 'total_count')) ?></h2>
                                <p class="mb-0">Son 30 gün içinde</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Aktif Müşteriler</h5>
                                <h2><?= count($active_customers) ?></h2>
                                <p class="mb-0">En yüksek cirolu müşteriler</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafikler -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Müşteri Grupları Analizi</h5>
                                <canvas id="customerGroupsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Etkileşim Türleri</h5>
                                <canvas id="interactionTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- En Aktif Müşteriler Tablosu -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">En Aktif Müşteriler</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Müşteri</th>
                                        <th>Firma</th>
                                        <th>Toplam Satış</th>
                                        <th>Toplam Not</th>
                                        <th>Toplam Görev</th>
                                        <th>Toplam Ciro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_customers as $customer): ?>
                                    <tr>
                                        <td><?= $customer['name'] ?></td>
                                        <td><?= $customer['company_name'] ?></td>
                                        <td><?= $customer['total_sales'] ?></td>
                                        <td><?= $customer['total_notes'] ?></td>
                                        <td><?= $customer['total_tasks'] ?></td>
                                        <td><?= number_format($customer['total_revenue'], 2) ?> ₺</td>
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
    <script>
        // Müşteri Grupları Grafiği
        new Chart(document.getElementById('customerGroupsChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($customer_groups, 'group_name')) ?>,
                datasets: [{
                    label: 'Müşteri Sayısı',
                    data: <?= json_encode(array_column($customer_groups, 'customer_count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Toplam Satış',
                    data: <?= json_encode(array_column($customer_groups, 'total_sales')) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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

        // Etkileşim Türleri Grafiği
        new Chart(document.getElementById('interactionTypesChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($interaction_stats, 'interaction_type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($interaction_stats, 'total_count')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html> 
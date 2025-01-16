<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Proje durumlarına göre özet
$project_summary = $conn->query("
    SELECT 
        status,
        COUNT(*) as total,
        AVG(progress) as avg_progress,
        SUM(CASE WHEN progress = 100 THEN 1 ELSE 0 END) as completed,
        AVG(DATEDIFF(due_date, start_date)) as avg_duration
    FROM production_orders
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Departman bazlı proje dağılımı
$department_projects = $conn->query("
    SELECT 
        d.name as department,
        COUNT(DISTINCT po.id) as total_projects,
        COUNT(DISTINCT CASE WHEN po.status = 'completed' THEN po.id END) as completed_projects,
        AVG(po.progress) as avg_progress
    FROM departments d
    LEFT JOIN employees e ON d.id = e.department_id
    LEFT JOIN project_assignments pa ON e.id = pa.employee_id
    LEFT JOIN production_orders po ON pa.project_id = po.id
    GROUP BY d.id, d.name
")->fetchAll(PDO::FETCH_ASSOC);

// Personel performans analizi
$employee_performance = $conn->query("
    SELECT 
        e.first_name,
        e.last_name,
        d.name as department,
        COUNT(DISTINCT pa.project_id) as total_projects,
        COUNT(DISTINCT CASE WHEN po.status = 'completed' THEN po.id END) as completed_projects,
        AVG(po.progress) as avg_project_progress,
        SUM(pa.estimated_days) as total_estimated_days
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN project_assignments pa ON e.id = pa.employee_id
    LEFT JOIN production_orders po ON pa.project_id = po.id
    WHERE e.status = 'active'
    GROUP BY e.id
    ORDER BY total_projects DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Raporları - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <div class="col-md-10 p-4">
                <h2 class="mb-4">Proje Raporları</h2>

                <!-- Özet Kartları -->
                <div class="row mb-4">
                    <?php foreach ($project_summary as $summary): ?>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title text-capitalize">
                                        <?= str_replace('_', ' ', $summary['status']) ?>
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="display-6"><?= $summary['total'] ?></div>
                                        <div class="text-end">
                                            <div>Ort. İlerleme: %<?= number_format($summary['avg_progress'], 1) ?></div>
                                            <small class="text-muted">
                                                Ort. Süre: <?= round($summary['avg_duration']) ?> gün
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row">
                    <!-- Departman Bazlı Analiz -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Departman Bazlı Proje Analizi</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Personel Performans Tablosu -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Personel Performans Analizi</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Personel</th>
                                                <th>Departman</th>
                                                <th>Toplam Proje</th>
                                                <th>Tamamlanan</th>
                                                <th>Ort. İlerleme</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employee_performance as $emp): ?>
                                                <tr>
                                                    <td><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></td>
                                                    <td><?= $emp['department'] ?></td>
                                                    <td><?= $emp['total_projects'] ?></td>
                                                    <td><?= $emp['completed_projects'] ?></td>
                                                    <td>%<?= number_format($emp['avg_project_progress'], 1) ?></td>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Departman bazlı proje grafiği
    const ctx = document.getElementById('departmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($department_projects, 'department')) ?>,
            datasets: [{
                label: 'Toplam Proje',
                data: <?= json_encode(array_column($department_projects, 'total_projects')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Tamamlanan Proje',
                data: <?= json_encode(array_column($department_projects, 'completed_projects')) ?>,
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
    </script>
</body>
</html> 
<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Personel iş yükü analizi
$workload = $conn->query("
    SELECT 
        e.id,
        e.first_name,
        e.last_name,
        d.name as department,
        COUNT(pa.id) as active_projects,
        SUM(pa.estimated_days) as total_estimated_days,
        GROUP_CONCAT(
            DISTINCT CONCAT(po.order_code, ': ', po.customer_name)
            SEPARATOR ', '
        ) as assigned_projects
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN project_assignments pa ON e.id = pa.employee_id
    LEFT JOIN production_orders po ON pa.project_id = po.id
    WHERE e.status = 'active'
    GROUP BY e.id
    ORDER BY d.name, e.first_name
")->fetchAll(PDO::FETCH_ASSOC);

// Departman bazlı kapasite analizi
$department_capacity = $conn->query("
    SELECT 
        d.name as department,
        COUNT(DISTINCT e.id) as total_employees,
        COUNT(DISTINCT pa.project_id) as active_projects,
        COALESCE(SUM(pa.estimated_days), 0) as total_workload,
        GREATEST((COUNT(DISTINCT e.id) * 20 * 8), 1) as monthly_capacity
    FROM departments d
    LEFT JOIN employees e ON d.id = e.department_id
    LEFT JOIN project_assignments pa ON e.id = pa.employee_id
    GROUP BY d.id, d.name
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaynak Planlama - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <div class="col-md-10 p-4">
                <h2 class="mb-4">Kaynak Planlama</h2>

                <!-- Departman Kapasite Analizi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Departman Kapasite Analizi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="departmentCapacityChart"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Departman</th>
                                                <th>Kapasite</th>
                                                <th>Kullanım</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($department_capacity as $dept): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($dept['department']) ?></td>
                                                    <td><?= $dept['monthly_capacity'] ?> saat</td>
                                                    <td>
                                                        <?php 
                                                        $usage = ($dept['total_workload'] / $dept['monthly_capacity']) * 100;
                                                        $class = $usage > 90 ? 'danger' : ($usage > 70 ? 'warning' : 'success');
                                                        ?>
                                                        <span class="badge bg-<?= $class ?>">
                                                            %<?= number_format($usage, 1) ?>
                                                        </span>
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

                <!-- Personel İş Yükü -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Personel İş Yükü Analizi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Personel</th>
                                        <th>Departman</th>
                                        <th>Aktif Projeler</th>
                                        <th>Toplam İş Günü</th>
                                        <th>Kapasite Kullanımı</th>
                                        <th>Detay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workload as $employee): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                                            <td><?= htmlspecialchars($employee['department']) ?></td>
                                            <td><?= $employee['active_projects'] ?></td>
                                            <td><?= $employee['total_estimated_days'] ?? 0 ?> gün</td>
                                            <td>
                                                <?php 
                                                $usage = (($employee['total_estimated_days'] ?? 0) / 20) * 100;
                                                $class = $usage > 90 ? 'danger' : ($usage > 70 ? 'warning' : 'success');
                                                ?>
                                                <div class="progress">
                                                    <div class="progress-bar bg-<?= $class ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= min($usage, 100) ?>%"
                                                         aria-valuenow="<?= $usage ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        %<?= number_format($usage, 1) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        onclick="showEmployeeDetails(<?= htmlspecialchars(json_encode($employee)) ?>)">
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

    <!-- Personel Detay Modalı -->
    <div class="modal fade" id="employeeDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Personel İş Yükü Detayı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="employeeName"></h6>
                    <p class="mb-2"><strong>Departman:</strong> <span id="employeeDepartment"></span></p>
                    <p class="mb-3"><strong>Atanan Projeler:</strong></p>
                    <p id="assignedProjects" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Departman kapasite grafiği
    const ctx = document.getElementById('departmentCapacityChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($department_capacity, 'department')) ?>,
            datasets: [{
                label: 'Kapasite Kullanımı (%)',
                data: <?= json_encode(array_map(function($dept) {
                    $monthly_capacity = max($dept['monthly_capacity'], 1);
                    $workload = floatval($dept['total_workload']);
                    return ($workload / $monthly_capacity) * 100;
                }, $department_capacity)) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    function showEmployeeDetails(employee) {
        document.getElementById('employeeName').textContent = 
            employee.first_name + ' ' + employee.last_name;
        document.getElementById('employeeDepartment').textContent = 
            employee.department;
        document.getElementById('assignedProjects').textContent = 
            employee.assigned_projects || 'Atanmış proje yok';
        
        new bootstrap.Modal(document.getElementById('employeeDetailsModal')).show();
    }
    </script>
</body>
</html> 
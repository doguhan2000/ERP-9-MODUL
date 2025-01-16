<?php
require_once '../../config/db.php';

// Departmanları getir
$query = "
    SELECT 
        d.id as department_id,
        d.name as department_name,
        e.id,
        e.first_name,
        e.last_name,
        e.email,
        e.phone,
        e.status,
        p.title as position_title
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    ORDER BY d.name ASC, p.title ASC";

$stmt = $conn->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Departmanlara göre çalışanları grupla
$departments = [];
foreach ($results as $row) {
    $deptId = $row['department_id'];
    if (!isset($departments[$deptId])) {
        $departments[$deptId] = [
            'name' => $row['department_name'],
            'employees' => []
        ];
    }
    if ($row['id']) {
        $departments[$deptId]['employees'][] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'position' => $row['position_title'],
            'status' => $row['status']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizasyon Şeması - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-sidebar {
            background: #2c3e50;
            min-height: 100vh;
            color: white;
            padding-top: 20px;
        }
        .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
        }
        .nav-link.active {
            background: #3498db;
            color: white;
        }
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1em;
        }
        .department-section {
            background: #fff;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        .department-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .department-header i {
            margin-right: 10px;
            color: #3498db;
        }
        .position-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f8f9fa;
            margin-left: 25px;
        }
        .employee-item {
            background: #f8f9fa;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 50px;
        }
        .employee-info {
            display: flex;
            flex-direction: column;
        }
        .employee-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .employee-email {
            color: #666;
            font-size: 0.9em;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .position-title {
            display: flex;
            align-items: center;
            color: #495057;
        }
        .position-title i {
            margin-right: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include '../includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Organizasyon Şeması</h2>
                </div>

                <?php foreach ($departments as $dept): ?>
                    <div class="department-section">
                        <div class="department-header">
                            <i class="bi bi-diagram-3"></i>
                            <strong><?= htmlspecialchars($dept['name']) ?></strong>
                        </div>
                        <?php
                        // Pozisyonlara göre grupla
                        $positions = [];
                        foreach ($dept['employees'] as $emp) {
                            $pos = $emp['position'] ?? 'Pozisyon Belirtilmemiş';
                            if (!isset($positions[$pos])) {
                                $positions[$pos] = [];
                            }
                            $positions[$pos][] = $emp;
                        }
                        
                        foreach ($positions as $position => $employees):
                        ?>
                            <div class="position-item">
                                <div class="position-title">
                                    <i class="bi bi-person-badge"></i>
                                    <?= htmlspecialchars($position) ?>
                                </div>
                                <?php foreach ($employees as $emp): ?>
                                    <div class="employee-item">
                                        <div class="employee-info">
                                            <div class="employee-name"><?= htmlspecialchars($emp['name']) ?></div>
                                            <div class="employee-email">
                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($emp['email']) ?>
                                            </div>
                                        </div>
                                        <span class="status-badge <?= $emp['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                            <?= $emp['status'] == 'active' ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

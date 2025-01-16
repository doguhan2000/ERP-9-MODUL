<?php
require_once '../../config/db.php';

// Departmanları ve çalışanları getir
$query = "
    SELECT 
        d.id as dept_id,
        d.name as dept_name,
        p.id as pos_id,
        p.title as pos_title,
        e.id as emp_id,
        e.first_name,
        e.last_name,
        e.email,
        e.phone,
        e.status
    FROM departments d
    LEFT JOIN positions p ON p.department_id = d.id
    LEFT JOIN employees e ON e.position_id = p.id AND e.department_id = d.id
    ORDER BY d.name, p.title";

$stmt = $conn->query($query);
$org_data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($org_data[$row['dept_id']])) {
        $org_data[$row['dept_id']] = [
            'name' => $row['dept_name'],
            'positions' => []
        ];
    }
    
    if ($row['pos_id'] && !isset($org_data[$row['dept_id']]['positions'][$row['pos_id']])) {
        $org_data[$row['dept_id']]['positions'][$row['pos_id']] = [
            'title' => $row['pos_title'],
            'employees' => []
        ];
    }
    
    if ($row['emp_id']) {
        $org_data[$row['dept_id']]['positions'][$row['pos_id']]['employees'][] = [
            'id' => $row['emp_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
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
    <title>Organizasyon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .org-tree ul {
            padding-left: 20px;
        }
        .org-tree li {
            list-style: none;
            margin: 10px 0;
            position: relative;
        }
        .org-tree li::before {
            content: "";
            position: absolute;
            top: 0;
            left: -20px;
            border-left: 1px solid #ccc;
            height: 100%;
        }
        .org-tree li::after {
            content: "";
            position: absolute;
            top: 15px;
            left: -20px;
            border-top: 1px solid #ccc;
            width: 20px;
        }
        .org-tree li:last-child::before {
            height: 15px;
        }
        .org-card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .department {
            background-color: #e3f2fd;
        }
        .position {
            background-color: #f5f5f5;
            margin-left: 20px;
        }
        .employee {
            background-color: #fff;
            margin-left: 40px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../index.php">9ERP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">İK Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="organization.php">Organizasyon Yönetimi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include '../../includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Organizasyon Şeması</h5>
                    </div>
                    <div class="card-body">
                        <div class="org-tree">
                            <?php foreach ($org_data as $dept): ?>
                                <div class="org-card department">
                                    <h5><i class="bi bi-diagram-3"></i> <?= htmlspecialchars($dept['name']) ?></h5>
                                    <?php if (!empty($dept['positions'])): ?>
                                        <ul>
                                            <?php foreach ($dept['positions'] as $pos): ?>
                                                <li>
                                                    <div class="org-card position">
                                                        <h6><i class="bi bi-person-badge"></i> <?= htmlspecialchars($pos['title']) ?></h6>
                                                        <?php if (!empty($pos['employees'])): ?>
                                                            <ul>
                                                                <?php foreach ($pos['employees'] as $emp): ?>
                                                                    <li>
                                                                        <div class="org-card employee">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <div>
                                                                                    <i class="bi bi-person"></i>
                                                                                    <strong><?= htmlspecialchars($emp['name']) ?></strong>
                                                                                </div>
                                                                                <span class="badge bg-<?= $emp['status'] == 'active' ? 'success' : 'warning' ?>">
                                                                                    <?= $emp['status'] == 'active' ? 'Aktif' : 'İzinde' ?>
                                                                                </span>
                                                                            </div>
                                                                            <small class="text-muted">
                                                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($emp['email']) ?><br>
                                                                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($emp['phone']) ?>
                                                                            </small>
                                                                        </div>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

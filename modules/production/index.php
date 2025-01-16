<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// İş istasyonlarını getir
$workstations = $conn->query("
    SELECT * FROM workstations 
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Aktif projeleri getir
$production_orders = $conn->query("
    SELECT po.*, w.name as workstation_name 
    FROM production_orders po
    LEFT JOIN workstations w ON po.workstation_id = w.id
    WHERE po.status != 'cancelled'
    ORDER BY po.priority DESC, po.due_date ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Proje Yönetimi</h2>
                    <button class="btn btn-primary" onclick="location.href='new_order.php'">
                        <i class="bi bi-plus-circle"></i> Yeni Proje Ekle
                    </button>
                </div>

                <!-- Departman Durumları -->
                <div class="row mb-4">
                    <?php foreach ($workstations as $station): ?>
                        <div class="col-md-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($station['name']) ?></h5>
                                    <p class="card-text">
                                        Kapasite: <?= $station['capacity'] ?> saat/ay<br>
                                        Durum: <span class="badge bg-<?= $station['status'] == 'active' ? 'success' : 'warning' ?>">
                                            <?= $station['status'] == 'active' ? 'Aktif' : 'Bakımda' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Aktif Projeler -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aktif Projeler</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proje Kodu</th>
                                        <th>Müşteri</th>
                                        <th>Proje Tipi</th>
                                        <th>Departman</th>
                                        <th>Termin</th>
                                        <th>Öncelik</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($production_orders as $order): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['order_code']) ?></td>
                                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($order['project_type']) ?></td>
                                            <td><?= htmlspecialchars($order['workstation_name']) ?></td>
                                            <td><?= date('d.m.Y', strtotime($order['due_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $order['priority'] == 'high' ? 'danger' : ($order['priority'] == 'medium' ? 'warning' : 'success') ?>">
                                                    <?= $order['priority'] == 'high' ? 'Yüksek' : ($order['priority'] == 'medium' ? 'Orta' : 'Düşük') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $order['status'] == 'completed' ? 'success' : ($order['status'] == 'in_progress' ? 'primary' : 'secondary') ?>">
                                                    <?= $order['status'] == 'completed' ? 'Tamamlandı' : ($order['status'] == 'in_progress' ? 'Devam Ediyor' : 'Beklemede') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
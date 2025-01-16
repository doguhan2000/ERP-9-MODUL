<?php
require_once '../../config/db.php';

// Seçilen tarihi al (varsayılan bugün)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Aktif personel listesini al
$stmt = $conn->query("
    SELECT 
        e.id, 
        e.first_name, 
        e.last_name,
        e.employee_no,
        d.name as department_name,
        p.title as position_title,
        tl.check_in,
        tl.check_out,
        tl.status,
        tl.total_hours
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    LEFT JOIN time_logs tl ON e.id = tl.employee_id 
        AND DATE(tl.check_in) = CURDATE()
    WHERE e.status = 'active'
    ORDER BY e.first_name, e.last_name
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İstatistikleri getir
$stats_query = "
    SELECT 
        COUNT(DISTINCT employee_id) as total_present,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
        SUM(CASE WHEN status = 'early_leave' THEN 1 ELSE 0 END) as early_leave_count,
        SUM(CASE WHEN status = 'normal' THEN 1 ELSE 0 END) as normal_count
    FROM time_logs 
    WHERE DATE(check_in) = ?";

$stmt = $conn->prepare($stats_query);
$stmt->execute([$selected_date]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Son 7 günün özetini getir
$weekly_stats_query = "
    SELECT 
        DATE(check_in) as date,
        COUNT(DISTINCT employee_id) as total_present,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
        SUM(CASE WHEN status = 'early_leave' THEN 1 ELSE 0 END) as early_leave_count,
        AVG(total_hours) as avg_hours
    FROM time_logs 
    WHERE DATE(check_in) BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ?
    GROUP BY DATE(check_in)
    ORDER BY date DESC";

$stmt = $conn->prepare($weekly_stats_query);
$stmt->execute([$selected_date, $selected_date]);
$weekly_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaman Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
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
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .stats-card i {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .employee-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 15px;
        }
        .employee-card .employee-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .employee-card .employee-details {
            flex-grow: 1;
        }
        .employee-card .time-inputs {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .status-normal { background: #d4edda; color: #155724; }
        .status-late { background: #fff3cd; color: #856404; }
        .status-early_leave { background: #f8d7da; color: #721c24; }
        .status-absent { background: #e2e3e5; color: #383d41; }
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
                    <h2>Zaman Yönetimi</h2>
                    <div class="d-flex align-items-center">
                        <input type="date" class="form-control me-2" 
                               value="<?= $selected_date ?>" 
                               onchange="changeDate(this.value)"
                               max="<?= date('Y-m-d') ?>">
                        <button class="btn btn-outline-primary" onclick="changeDate('<?= date('Y-m-d') ?>')">
                            Bugün
                        </button>
                    </div>
                </div>

                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-primary">
                            <i class="bi bi-people"></i>
                            <h3 class="mb-2"><?= $stats['total_present'] ?? 0 ?></h3>
                            <p class="mb-0">Bugün İşte</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-success">
                            <i class="bi bi-check-circle"></i>
                            <h3 class="mb-2"><?= $stats['normal_count'] ?? 0 ?></h3>
                            <p class="mb-0">Normal Mesai</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-warning">
                            <i class="bi bi-exclamation-circle"></i>
                            <h3 class="mb-2"><?= $stats['late_count'] ?? 0 ?></h3>
                            <p class="mb-0">Geç Gelen</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-danger">
                            <i class="bi bi-door-open"></i>
                            <h3 class="mb-2"><?= $stats['early_leave_count'] ?? 0 ?></h3>
                            <p class="mb-0">Erken Çıkan</p>
                        </div>
                    </div>
                </div>

                <!-- Son 7 Gün Özeti -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Son 7 Gün Özeti</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Toplam Personel</th>
                                        <th>Geç Gelen</th>
                                        <th>Erken Çıkan</th>
                                        <th>Ort. Çalışma Süresi</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($weekly_stats as $stat): ?>
                                    <tr <?= $stat['date'] == $selected_date ? 'class="table-active"' : '' ?>>
                                        <td><?= date('d.m.Y', strtotime($stat['date'])) ?></td>
                                        <td><?= $stat['total_present'] ?></td>
                                        <td>
                                            <?php if ($stat['late_count'] > 0): ?>
                                                <span class="text-warning"><?= $stat['late_count'] ?></span>
                                            <?php else: ?>
                                                <span class="text-success">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($stat['early_leave_count'] > 0): ?>
                                                <span class="text-danger"><?= $stat['early_leave_count'] ?></span>
                                            <?php else: ?>
                                                <span class="text-success">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format($stat['avg_hours'], 2) ?> saat</td>
                                        <td>
                                            <button class="btn btn-sm btn-link" onclick="changeDate('<?= $stat['date'] ?>')">
                                                Görüntüle
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Personel Listesi -->
                <div class="row">
                    <?php foreach ($employees as $emp): ?>
                    <div class="col-md-6">
                        <div class="employee-card">
                            <div class="employee-info">
                                <div class="employee-details">
                                    <h5 class="mb-1">
                                        <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($emp['employee_no']) ?> - 
                                        <?= htmlspecialchars($emp['department_name']) ?> - 
                                        <?= htmlspecialchars($emp['position_title']) ?>
                                    </small>
                                </div>
                                <?php if ($emp['check_in']): ?>
                                    <span class="status-badge status-<?= $emp['status'] ?>">
                                        <?php
                                        $status_text = [
                                            'normal' => 'Normal',
                                            'late' => 'Geç',
                                            'early_leave' => 'Erken Çıkış',
                                            'absent' => 'Gelmedi'
                                        ];
                                        echo $status_text[$emp['status']] ?? $emp['status'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="time-inputs">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-arrow-in-right"></i></span>
                                    <input type="time" class="form-control" 
                                           value="<?= $emp['check_in'] ? date('H:i', strtotime($emp['check_in'])) : '' ?>"
                                           onchange="updateTime(<?= $emp['id'] ?>, 'check_in', this.value)">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-arrow-right"></i></span>
                                    <input type="time" class="form-control"
                                           value="<?= $emp['check_out'] ? date('H:i', strtotime($emp['check_out'])) : '' ?>"
                                           onchange="updateTime(<?= $emp['id'] ?>, 'check_out', this.value)">
                                </div>
                                <?php if ($emp['total_hours']): ?>
                                    <span class="ms-2 text-muted">
                                        <?= number_format($emp['total_hours'], 2) ?> saat
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeDate(date) {
            window.location.href = 'index.php?date=' + date;
        }

        function updateTime(employeeId, type, time) {
            if (!time) return;

            const selectedDate = '<?= $selected_date ?>';
            const datetime = selectedDate + ' ' + time;

            fetch('api/time_log.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `employee_id=${employeeId}&${type}=${datetime}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }
    </script>
</body>
</html>

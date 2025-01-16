<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Projeleri getir
$projects = $conn->query("
    SELECT 
        po.*, 
        COUNT(DISTINCT pa.employee_id) as assigned_employees,
        GROUP_CONCAT(
            DISTINCT CONCAT(e.first_name, ' ', e.last_name) 
            SEPARATOR ', '
        ) as employees,
        COALESCE(po.progress, 0) as progress
    FROM production_orders po
    LEFT JOIN project_assignments pa ON po.id = pa.project_id
    LEFT JOIN employees e ON pa.employee_id = e.id
    GROUP BY po.id
    ORDER BY po.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Personelleri getir
$employees = $conn->query("
    SELECT e.*, d.name as department_name 
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.status = 'active'
    ORDER BY e.first_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel-Proje Atamaları - 9ERP</title>
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
                    <h2>Personel-Proje Atamaları</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                        <i class="bi bi-plus-lg"></i> Yeni Atama
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proje Kodu</th>
                                        <th>Müşteri</th>
                                        <th>Proje Tipi</th>
                                        <th>Atanan Personeller</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($project['order_code']) ?></td>
                                        <td><?= htmlspecialchars($project['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($project['project_type']) ?></td>
                                        <td><?= htmlspecialchars($project['employees'] ?: 'Henüz atama yapılmadı') ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-<?= $project['status'] == 'completed' ? 'success' : 
                                                        ($project['status'] == 'in_progress' ? 'primary' : 'secondary') ?>"
                                                    onclick="updateStatus(<?= htmlspecialchars(json_encode([
                                                        'id' => $project['id'],
                                                        'status' => $project['status'],
                                                        'progress' => $project['progress'] ?? 0,
                                                        'order_code' => $project['order_code'],
                                                        'customer_name' => $project['customer_name']
                                                    ])) ?>)">
                                                <?= $project['status'] == 'completed' ? 'Tamamlandı' : 
                                                    ($project['status'] == 'in_progress' ? 'Devam Ediyor' : 'Beklemede') ?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                onclick="showDetails(<?= htmlspecialchars(json_encode($project)) ?>)">
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

    <!-- Yeni Atama Modalı -->
    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Personel Ataması</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignmentForm">
                        <div class="mb-3">
                            <label class="form-label">Proje</label>
                            <select class="form-select" name="project_id" required>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project['id'] ?>">
                                        <?= htmlspecialchars($project['order_code'] . ' - ' . $project['customer_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Personel</label>
                            <select class="form-select" name="employee_id" required>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['id'] ?>">
                                        <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . 
                                            ' (' . $employee['department_name'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bitiş Tarihi</label>
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahmin Edilen Süre (Gün)</label>
                            <input type="number" class="form-control" name="estimated_days" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveAssignment()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detay Modalı -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proje Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Proje Bilgileri</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Proje Kodu:</th>
                                    <td id="orderCode"></td>
                                </tr>
                                <tr>
                                    <th>Müşteri:</th>
                                    <td id="customerName"></td>
                                </tr>
                                <tr>
                                    <th>Firma:</th>
                                    <td id="companyName"></td>
                                </tr>
                                <tr>
                                    <th>E-posta:</th>
                                    <td id="contactEmail"></td>
                                </tr>
                                <tr>
                                    <th>Telefon:</th>
                                    <td id="contactPhone"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Proje Detayları</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Proje Tipi:</th>
                                    <td id="projectType"></td>
                                </tr>
                                <tr>
                                    <th>Başlangıç:</th>
                                    <td id="startDate"></td>
                                </tr>
                                <tr>
                                    <th>Termin:</th>
                                    <td id="dueDate"></td>
                                </tr>
                                <tr>
                                    <th>Atanan Personeller:</th>
                                    <td id="assignedEmployees"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Teknik Detaylar</h6>
                            <div class="mb-2">
                                <strong>İstenen Özellikler:</strong>
                                <p id="features" class="mb-2"></p>
                            </div>
                            <div>
                                <strong>Teknoloji Stack:</strong>
                                <p id="techStack" class="mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function saveAssignment() {
        const form = document.getElementById('assignmentForm');
        const formData = new FormData(form);

        fetch('api/save_assignment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    function showDetails(project) {
        document.getElementById('orderCode').textContent = project.order_code;
        document.getElementById('customerName').textContent = project.customer_name;
        document.getElementById('companyName').textContent = project.company_name || '-';
        document.getElementById('contactEmail').textContent = project.contact_email || '-';
        document.getElementById('contactPhone').textContent = project.contact_phone || '-';
        document.getElementById('projectType').textContent = project.project_type;
        document.getElementById('startDate').textContent = new Date(project.start_date).toLocaleDateString('tr-TR');
        document.getElementById('dueDate').textContent = new Date(project.due_date).toLocaleDateString('tr-TR');
        document.getElementById('assignedEmployees').textContent = project.employees || 'Henüz atama yapılmadı';
        document.getElementById('features').textContent = project.features || '-';
        document.getElementById('techStack').textContent = project.tech_stack || '-';

        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
    </script>
</body>
</html> 
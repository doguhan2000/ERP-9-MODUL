<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Projeleri ve atanan personelleri getir
$projects = $conn->query("
    SELECT 
        po.*,
        GROUP_CONCAT(
            DISTINCT CONCAT(e.first_name, ' ', e.last_name) 
            SEPARATOR ', '
        ) as employees
    FROM production_orders po
    LEFT JOIN project_assignments pa ON po.id = pa.project_id
    LEFT JOIN employees e ON pa.employee_id = e.id
    GROUP BY po.id
    ORDER BY po.start_date ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Takvimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Proje Takvimi</h2>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2" id="listView">
                            <i class="bi bi-list"></i> Liste Görünümü
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="calendarView">
                            <i class="bi bi-calendar3"></i> Takvim Görünümü
                        </button>
                    </div>
                </div>

                <!-- Liste Görünümü -->
                <div id="projectList" class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proje Kodu</th>
                                        <th>Müşteri</th>
                                        <th>Başlangıç</th>
                                        <th>Bitiş</th>
                                        <th>Atanan Personeller</th>
                                        <th>Durum</th>
                                        <th>İlerleme</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($project['order_code']) ?></td>
                                        <td><?= htmlspecialchars($project['customer_name']) ?></td>
                                        <td><?= date('d.m.Y', strtotime($project['start_date'])) ?></td>
                                        <td><?= date('d.m.Y', strtotime($project['due_date'])) ?></td>
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
                                            <div class="progress" style="cursor: pointer;" 
                                                 onclick="updateProgress(<?= htmlspecialchars(json_encode([
                                                     'id' => $project['id'],
                                                     'progress' => $project['progress'] ?? 0,
                                                     'order_code' => $project['order_code']
                                                 ])) ?>)">
                                                <div class="progress-bar" role="progressbar" 
                                                    style="width: <?= $project['progress'] ?? 0 ?>%"
                                                    aria-valuenow="<?= $project['progress'] ?? 0 ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= $project['progress'] ?? 0 ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Takvim Görünümü -->
                <div id="calendar" class="card d-none">
                    <div class="card-body">
                        <div id="projectCalendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Durum Güncelleme Modalı -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proje Durumu Güncelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" name="project_id" id="statusProjectId">
                        <div class="mb-3">
                            <label class="form-label">Proje</label>
                            <input type="text" class="form-control" id="statusProjectName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status" id="statusSelect">
                                <option value="pending">Beklemede</option>
                                <option value="in_progress">Devam Ediyor</option>
                                <option value="completed">Tamamlandı</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İlerleme (%)</label>
                            <input type="number" class="form-control" name="progress" id="statusProgress" 
                                   min="0" max="100" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveStatus()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- İlerleme Güncelleme Modalı -->
    <div class="modal fade" id="progressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proje İlerlemesi Güncelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="progressForm">
                        <input type="hidden" name="project_id" id="progressProjectId">
                        <div class="mb-3">
                            <label class="form-label">Proje</label>
                            <input type="text" class="form-control" id="progressProjectName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İlerleme (%)</label>
                            <input type="range" class="form-range" name="progress" id="progressRange" 
                                   min="0" max="100" oninput="this.nextElementSibling.value = this.value">
                            <output>0</output>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveProgress()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Görünüm değiştirme butonları
        document.getElementById('listView').addEventListener('click', function() {
            document.getElementById('projectList').classList.remove('d-none');
            document.getElementById('calendar').classList.add('d-none');
        });

        document.getElementById('calendarView').addEventListener('click', function() {
            document.getElementById('projectList').classList.add('d-none');
            document.getElementById('calendar').classList.remove('d-none');
            calendar.render();
        });

        // Takvim oluşturma
        var calendarEl = document.getElementById('projectCalendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'tr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: <?= json_encode(array_map(function($project) {
                return [
                    'title' => $project['order_code'] . ' - ' . $project['customer_name'],
                    'start' => $project['start_date'],
                    'end' => $project['due_date'],
                    'backgroundColor' => $project['status'] == 'completed' ? '#198754' : 
                        ($project['status'] == 'in_progress' ? '#0d6efd' : '#6c757d')
                ];
            }, $projects)) ?>
        });
    });

    function updateStatus(project) {
        document.getElementById('statusProjectId').value = project.id;
        document.getElementById('statusProjectName').value = project.order_code + ' - ' + project.customer_name;
        document.getElementById('statusSelect').value = project.status;
        document.getElementById('statusProgress').value = project.progress;
        
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function updateProgress(project) {
        document.getElementById('progressProjectId').value = project.id;
        document.getElementById('progressProjectName').value = project.order_code;
        document.getElementById('progressRange').value = project.progress;
        document.getElementById('progressRange').nextElementSibling.value = project.progress;
        
        new bootstrap.Modal(document.getElementById('progressModal')).show();
    }

    function saveStatus() {
        const form = document.getElementById('statusForm');
        const formData = new FormData(form);

        fetch('api/update_project_status.php', {
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

    function saveProgress() {
        const form = document.getElementById('progressForm');
        const formData = new FormData(form);

        fetch('api/update_project_progress.php', {
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
    </script>
</body>
</html> 
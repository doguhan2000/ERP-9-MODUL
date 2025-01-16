<?php
require_once '../../config/db.php';

// Görevleri getir
$tasks = $conn->query("
    SELECT 
        t.*,
        c.name as customer_name,
        c.company_name
    FROM tasks t
    LEFT JOIN customers c ON t.customer_id = c.id
    WHERE t.status != 'deleted'
    ORDER BY t.due_date ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Görevler - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Görevler</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                        <i class="bi bi-plus-circle"></i> Yeni Görev
                    </button>
                </div>

                <!-- Görev Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Müşteri</th>
                                        <th>Tip</th>
                                        <th>Başlangıç</th>
                                        <th>Bitiş</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($task['title']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($task['customer_name']) ?>
                                            <?php if ($task['company_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($task['company_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $tipTurkce = [
                                                'meeting' => 'Toplantı',
                                                'call' => 'Telefon',
                                                'visit' => 'Ziyaret',
                                                'follow_up' => 'Takip',
                                                'other' => 'Diğer'
                                            ];
                                            echo $tipTurkce[$task['type']] ?? $task['type'];
                                            ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($task['start_date'])) ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($task['due_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $task['status'] == 'completed' ? 'success' : 
                                                ($task['status'] == 'in_progress' ? 'primary' : 'warning') ?>">
                                                <?php
                                                $durumTurkce = [
                                                    'pending' => 'Bekliyor',
                                                    'in_progress' => 'Devam Ediyor',
                                                    'completed' => 'Tamamlandı',
                                                    'deleted' => 'Silindi'
                                                ];
                                                echo $durumTurkce[$task['status']] ?? $task['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTask(<?= $task['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="completeTask(<?= $task['id'] ?>)">
                                                <i class="bi bi-check-lg"></i>
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

    <!-- Yeni Görev Modalı -->
    <div class="modal fade" id="newTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Görev Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newTaskForm">
                        <div class="mb-3">
                            <label class="form-label">Müşteri</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Müşteri Seçin</option>
                                <?php
                                $customers = $conn->query("SELECT id, name, company_name FROM customers WHERE status = 'active' ORDER BY name")->fetchAll();
                                foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['name']) ?> 
                                        <?= $customer['company_name'] ? '(' . htmlspecialchars($customer['company_name']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tip</label>
                            <select class="form-select" name="type" required>
                                <option value="meeting">Toplantı</option>
                                <option value="call">Telefon</option>
                                <option value="visit">Ziyaret</option>
                                <option value="follow_up">Takip</option>
                                <option value="other">Diğer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control" name="due_date" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveTask()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Görev Detay Modalı -->
    <div class="modal fade" id="taskDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Görev Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="taskDetailsContent">
                    <!-- Detaylar JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function saveTask() {
        const formData = new FormData(document.getElementById('newTaskForm'));
        
        fetch('api/add_task.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Görev başarıyla eklendi!');
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }

    function viewTask(taskId) {
        fetch(`api/get_task_details.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const task = data.task;
                    const tipTurkce = {
                        'meeting': 'Toplantı',
                        'call': 'Telefon',
                        'visit': 'Ziyaret',
                        'follow_up': 'Takip',
                        'other': 'Diğer'
                    };
                    const durumTurkce = {
                        'pending': 'Bekliyor',
                        'in_progress': 'Devam Ediyor',
                        'completed': 'Tamamlandı',
                        'deleted': 'Silindi'
                    };
                    const html = `
                        <p><strong>Başlık:</strong> ${task.title}</p>
                        <p><strong>Müşteri:</strong> ${task.customer_name}</p>
                        <p><strong>Açıklama:</strong> ${task.description || '-'}</p>
                        <p><strong>Tip:</strong> ${tipTurkce[task.type] || task.type}</p>
                        <p><strong>Başlangıç:</strong> ${new Date(task.start_date).toLocaleString('tr-TR')}</p>
                        <p><strong>Bitiş:</strong> ${new Date(task.due_date).toLocaleString('tr-TR')}</p>
                        <p><strong>Durum:</strong> ${durumTurkce[task.status] || task.status}</p>
                    `;
                    document.getElementById('taskDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('taskDetailsModal')).show();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
    }

    function completeTask(taskId) {
        if (!confirm('Görevi tamamlandı olarak işaretlemek istediğinize emin misiniz?')) {
            return;
        }

        fetch('api/complete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ task_id: taskId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }
    </script>
</body>
</html> 
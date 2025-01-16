<?php
require_once '../../config/db.php';
$page_title = "Zaman Yönetimi";

// Aktif personel listesini al
$stmt = $conn->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name, last_name");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vardiyaları al
$stmt = $conn->query("SELECT * FROM shifts ORDER BY start_time");
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İzin türlerini al
$stmt = $conn->query("SELECT * FROM leave_types ORDER BY name");
$leave_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Son puantaj kayıtlarını al
$stmt = $conn->query("
    SELECT ar.*, 
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           s.name as shift_name
    FROM attendance_records ar
    JOIN employees e ON ar.employee_id = e.id
    LEFT JOIN shifts s ON ar.shift_id = s.id
    ORDER BY ar.check_in DESC
    LIMIT 10
");
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bekleyen izin taleplerini al
$stmt = $conn->query("
    SELECT el.*,
           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
           lt.name as leave_type
    FROM employee_leaves el
    JOIN employees e ON el.employee_id = e.id
    JOIN leave_types lt ON el.leave_type_id = lt.id
    WHERE el.status = 'pending'
    ORDER BY el.created_at DESC
");
$pending_leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaman Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2">
                <?php include '../../includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10">
                <div class="row mb-3">
                    <div class="col">
                        <h2>Zaman Yönetimi</h2>
                    </div>
                </div>

                <!-- Hızlı İşlemler -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Giriş Kaydı</h5>
                                <button class="btn btn-primary" onclick="checkIn()">
                                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Çıkış Kaydı</h5>
                                <button class="btn btn-warning" onclick="checkOut()">
                                    <i class="bi bi-box-arrow-left"></i> Çıkış Yap
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">İzin Talebi</h5>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#leaveRequestModal">
                                    <i class="bi bi-calendar-plus"></i> İzin Talep Et
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Fazla Mesai</h5>
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#overtimeModal">
                                    <i class="bi bi-clock"></i> Mesai Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sekmeler -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#attendance">
                            Puantaj Kayıtları
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leaves">
                            İzin Talepleri
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shifts">
                            Vardiya Planı
                        </button>
                    </li>
                </ul>

                <!-- Sekme İçerikleri -->
                <div class="tab-content mt-3">
                    <!-- Puantaj Kayıtları -->
                    <div class="tab-pane fade show active" id="attendance">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Son Puantaj Kayıtları</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Personel</th>
                                                <th>Giriş</th>
                                                <th>Çıkış</th>
                                                <th>Vardiya</th>
                                                <th>Gecikme</th>
                                                <th>Erken Çıkış</th>
                                                <th>Fazla Mesai</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_records as $record): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($record['employee_name']) ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($record['check_in'])) ?></td>
                                                <td><?= $record['check_out'] ? date('d.m.Y H:i', strtotime($record['check_out'])) : '-' ?></td>
                                                <td><?= htmlspecialchars($record['shift_name']) ?></td>
                                                <td><?= $record['late_minutes'] > 0 ? $record['late_minutes'] . ' dk' : '-' ?></td>
                                                <td><?= $record['early_leave_minutes'] > 0 ? $record['early_leave_minutes'] . ' dk' : '-' ?></td>
                                                <td><?= $record['overtime_minutes'] > 0 ? $record['overtime_minutes'] . ' dk' : '-' ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'present' => 'success',
                                                        'absent' => 'danger',
                                                        'late' => 'warning',
                                                        'half_day' => 'info'
                                                    ];
                                                    $status_text = [
                                                        'present' => 'Tam Gün',
                                                        'absent' => 'Yok',
                                                        'late' => 'Geç',
                                                        'half_day' => 'Yarım Gün'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $status_class[$record['status']] ?>">
                                                        <?= $status_text[$record['status']] ?>
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

                    <!-- İzin Talepleri -->
                    <div class="tab-pane fade" id="leaves">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Bekleyen İzin Talepleri</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Personel</th>
                                                <th>İzin Türü</th>
                                                <th>Başlangıç</th>
                                                <th>Bitiş</th>
                                                <th>Gün</th>
                                                <th>Sebep</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_leaves as $leave): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($leave['employee_name']) ?></td>
                                                <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                                                <td><?= date('d.m.Y', strtotime($leave['start_date'])) ?></td>
                                                <td><?= date('d.m.Y', strtotime($leave['end_date'])) ?></td>
                                                <td><?= $leave['total_days'] ?></td>
                                                <td><?= htmlspecialchars($leave['reason']) ?></td>
                                                <td>
                                                    <button class="btn btn-success btn-sm" onclick="approveLeave(<?= $leave['id'] ?>)">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="rejectLeave(<?= $leave['id'] ?>)">
                                                        <i class="bi bi-x-lg"></i>
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

                    <!-- Vardiya Planı -->
                    <div class="tab-pane fade" id="shifts">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Vardiya Planı</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
                                        <i class="bi bi-plus-lg"></i> Vardiya Ata
                                    </button>
                                </div>
                                <div id="shiftCalendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- İzin Talep Modalı -->
    <div class="modal fade" id="leaveRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">İzin Talebi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="leaveRequestForm">
                        <div class="mb-3">
                            <label class="form-label">İzin Türü</label>
                            <select class="form-control" name="leave_type_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($leave_types as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sebep</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="submitLeaveRequest()">Gönder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vardiya Atama Modalı -->
    <div class="modal fade" id="assignShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vardiya Ata</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignShiftForm">
                        <div class="mb-3">
                            <label class="form-label">Personel</label>
                            <select class="form-control" name="employee_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vardiya</label>
                            <select class="form-control" name="shift_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($shifts as $shift): ?>
                                <option value="<?= $shift['id'] ?>"><?= htmlspecialchars($shift['name']) ?> (<?= substr($shift['start_time'], 0, 5) ?>-<?= substr($shift['end_time'], 0, 5) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="assignShift()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

    <script>
    // Giriş Kaydı
    function checkIn() {
        fetch('api/attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'check_in' })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Giriş kaydı başarıyla oluşturuldu!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // Çıkış Kaydı
    function checkOut() {
        fetch('api/attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ action: 'check_out' })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Çıkış kaydı başarıyla oluşturuldu!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // İzin Talebi Gönderme
    function submitLeaveRequest() {
        const form = document.getElementById('leaveRequestForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        fetch('api/leave.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('İzin talebi başarıyla gönderildi!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // Vardiya Atama
    function assignShift() {
        const form = document.getElementById('assignShiftForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        fetch('api/shift.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Vardiya ataması başarıyla yapıldı!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // İzin Onaylama
    function approveLeave(id) {
        if (confirm('Bu izin talebini onaylamak istediğinizden emin misiniz?')) {
            fetch('api/leave.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    action: 'approve'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('İzin talebi onaylandı!');
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            });
        }
    }

    // İzin Reddetme
    function rejectLeave(id) {
        if (confirm('Bu izin talebini reddetmek istediğinizden emin misiniz?')) {
            fetch('api/leave.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    action: 'reject'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('İzin talebi reddedildi!');
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            });
        }
    }

    // Vardiya Takvimi
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('shiftCalendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'tr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: 'api/shift.php?action=get_shifts',
            eventClick: function(info) {
                alert('Vardiya: ' + info.event.title);
            }
        });
        calendar.render();
    });
    </script>
</body>
</html>

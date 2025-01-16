<?php
require_once '../../config/db.php';

// Personel listesini al
$query = "
    SELECT 
        e.*,
        d.name as department_name, 
        p.title as position_title,
        CONCAT(m.first_name, ' ', m.last_name) as manager_name,
        (
            SELECT COUNT(*) 
            FROM employees sub WHERE sub.manager_id = e.id
        ) as subordinates_count
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    LEFT JOIN employees m ON e.manager_id = m.id
    ORDER BY e.id DESC";

$stmt = $conn->query($query);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Departman listesini al
$stmt = $conn->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pozisyon listesini al
$stmt = $conn->query("SELECT * FROM positions ORDER BY title");
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Yönetici olabilecek personel listesini al
$stmt = $conn->query("
    SELECT id, first_name, last_name 
    FROM employees 
    WHERE status = 'active'
    ORDER BY first_name, last_name
");
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İstatistikleri getir
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_employees,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
        SUM(CASE WHEN status = 'passive' THEN 1 ELSE 0 END) as passive_employees,
        (SELECT COUNT(*) FROM departments) as department_count
    FROM employees
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
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
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .employee-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }
        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .employee-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        .department-tag {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.85em;
            color: #6c757d;
        }
        .action-buttons {
            opacity: 0;
            transition: opacity 0.3s;
        }
        .employee-card:hover .action-buttons {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Ana Menü -->
            <div class="col-md-2 main-sidebar">
                <div class="d-flex align-items-center mb-4 px-3">
                    <h4 class="mb-0">9ERP</h4>
                </div>
                <div class="nav flex-column">
                    <a class="nav-link" href="../hr/index.php">
                        <i class="bi bi-people"></i> Personel Yönetimi
                    </a>
                    <a class="nav-link" href="../organization/index.php">
                        <i class="bi bi-diagram-2"></i> Organizasyon Şeması
                    </a>
                    <a class="nav-link" href="../performance/index.php">
                        <i class="bi bi-graph-up"></i> Performans Yönetimi
                    </a>
                    <a class="nav-link" href="../time/index.php">
                        <i class="bi bi-calendar3"></i> Zaman Yönetimi
                    </a>
                    <a class="nav-link" href="../hr/bordro.php">
                        <i class="bi bi-cash-stack"></i> Bordro ve Özlük
                    </a>
                    <a class="nav-link" href="../recruitment/index.php">
                        <i class="bi bi-person-plus"></i> İşe Alım
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Personel Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                        <i class="bi bi-plus-lg"></i> Yeni Personel
                    </button>
                </div>

                <!-- İstatistikler -->
                <div class="row mb-4" style="background-color: #f8f9fa; padding: 15px; border-radius: 10px;">
                    <div class="col-md-3">
                        <div class="card stats-card rounded-4" style="background-color: #4475F2; border: none; height: 100px;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">Toplam Personel</div>
                                    <div class="fs-2 fw-bold text-white"><?= $stats['total_employees'] ?></div>
                                </div>
                                <div class="stats-icon opacity-75">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card rounded-4" style="background-color: #38A169; border: none; height: 100px;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">Aktif Personel</div>
                                    <div class="fs-2 fw-bold text-white"><?= $stats['active_employees'] ?></div>
                                </div>
                                <div class="stats-icon opacity-75">
                                    <i class="bi bi-person-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card rounded-4" style="background-color: #E53E3E; border: none; height: 100px;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">Pasif Personel</div>
                                    <div class="fs-2 fw-bold text-white"><?= $stats['passive_employees'] ?></div>
                                </div>
                                <div class="stats-icon opacity-75">
                                    <i class="bi bi-person-dash"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card rounded-4" style="background-color: #ECC94B; border: none; height: 100px;">
                            <div class="card-body d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div style="color: rgba(255,255,255,0.9); font-size: 0.9rem;">Departman Sayısı</div>
                                    <div class="fs-2 fw-bold text-white"><?= $stats['department_count'] ?></div>
                                </div>
                                <div class="stats-icon opacity-75">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personel Listesi -->
                <div class="row g-4">
                    <?php foreach ($employees as $employee): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card employee-card">
                            <div class="card-body">
                                <span class="status-badge bg-<?= $employee['status'] === 'active' ? 'success' : 
                                    ($employee['status'] === 'passive' ? 'danger' : 'warning') ?>">
                                    <?= $employee['status'] === 'active' ? 'Aktif' : 
                                        ($employee['status'] === 'passive' ? 'Pasif' : 'İzinde') ?>
                                </span>
                                <div class="text-center mb-3">
                                    <?php if ($employee['photo']): ?>
                                        <img src="<?= 'uploads/' . htmlspecialchars($employee['photo']) ?>" 
                                             class="employee-photo mb-2" 
                                             alt="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>">
                                    <?php else: ?>
                                        <div class="employee-photo mx-auto mb-2 d-flex align-items-center justify-content-center bg-light">
                                            <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="mb-1"><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h5>
                                    <small class="text-muted"><?= htmlspecialchars($employee['employee_no']) ?></small>
                                </div>
                                <div class="mb-3">
                                    <div class="department-tag mb-2">
                                        <i class="bi bi-diagram-3"></i> <?= htmlspecialchars($employee['department_name'] ?? 'Departman Yok') ?>
                                    </div>
                                    <div class="department-tag">
                                        <i class="bi bi-briefcase"></i> <?= htmlspecialchars($employee['position_title'] ?? 'Pozisyon Yok') ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewEmployee(<?= $employee['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editEmployee(<?= $employee['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="toggleStatus(<?= $employee['id'] ?>)">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(<?= $employee['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Ekleme Modalı -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Personel Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Fotoğraf</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ad</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Soyad</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departman</label>
                                    <select class="form-control" name="department_id" required>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pozisyon</label>
                                    <select class="form-control" name="position_id" required>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($positions as $pos): ?>
                                        <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">İşe Başlama Tarihi</label>
                                    <input type="date" class="form-control" name="hire_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maaş</label>
                                    <input type="number" class="form-control" name="salary" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="addEmployee()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Düzenleme Modalı -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Personel Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmployeeForm">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ad</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Soyad</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departman</label>
                                    <select class="form-control" id="edit_department_id" name="department_id" required>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pozisyon</label>
                                    <select class="form-control" id="edit_position_id" name="position_id" required>
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($positions as $pos): ?>
                                        <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">İşe Başlama Tarihi</label>
                                    <input type="date" class="form-control" id="edit_hire_date" name="hire_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maaş</label>
                                    <input type="number" class="form-control" id="edit_salary" name="salary" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateEmployee()">Güncelle</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Personel Görüntüleme Modalı -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Personel Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewEmployeeContent">
                    <!-- İçerik JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Personel Ekleme
    function addEmployee() {
        const form = document.getElementById('addEmployeeForm');
        const formData = new FormData(form);

        fetch('api/employee.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Personel başarıyla eklendi!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // Personel Düzenleme
    function editEmployee(id) {
        fetch(`api/employee.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                
                // Form alanlarını doldur
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_first_name').value = data.first_name;
                document.getElementById('edit_last_name').value = data.last_name;
                document.getElementById('edit_email').value = data.email;
                document.getElementById('edit_phone').value = data.phone;
                document.getElementById('edit_department_id').value = data.department_id;
                document.getElementById('edit_position_id').value = data.position_id;
                document.getElementById('edit_hire_date').value = data.hire_date;
                document.getElementById('edit_salary').value = data.salary;

                // Modalı göster
                new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
            }
        });
    }

    // Personel Güncelleme
    function updateEmployee() {
        const form = document.getElementById('editEmployeeForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        fetch('api/employee.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Personel başarıyla güncellendi!');
                location.reload();
            } else {
                alert('Hata: ' + result.message);
            }
        });
    }

    // Personel Görüntüleme
    function viewEmployee(id) {
        fetch(`api/employee.php?id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                
                // Modal içeriğini oluştur
                document.getElementById('viewEmployeeContent').innerHTML = `
                    <div class="text-center mb-3">
                        <img src="${data.photo ? 'uploads/' + data.photo : 'https://via.placeholder.com/150'}" 
                             class="rounded-circle" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ad Soyad:</strong> ${data.first_name} ${data.last_name}</p>
                            <p><strong>E-posta:</strong> ${data.email}</p>
                            <p><strong>Telefon:</strong> ${data.phone || '-'}</p>
                            <p><strong>Personel No:</strong> ${data.employee_no}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Departman:</strong> ${data.department_name || '-'}</p>
                            <p><strong>Pozisyon:</strong> ${data.position_title || '-'}</p>
                            <p><strong>Yönetici:</strong> ${data.manager_name || '-'}</p>
                            <p><strong>İşe Başlama:</strong> ${data.hire_date || '-'}</p>
                            <p><strong>Maaş:</strong> ${data.salary || '-'}</p>
                        </div>
                    </div>
                `;

                // Modalı göster
                new bootstrap.Modal(document.getElementById('viewEmployeeModal')).show();
            }
        });
    }

    // Personel Durumu Değiştirme
    function toggleStatus(id) {
        if (confirm('Personelin durumunu değiştirmek istediğinizden emin misiniz?')) {
            fetch('api/employee.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: 'toggle'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            });
        }
    }

    // Personel Silme
    function deleteEmployee(id) {
        if (confirm('Bu personeli silmek istediğinizden emin misiniz?')) {
            fetch(`api/employee.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Personel başarıyla silindi!');
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            });
        }
    }
    </script>
</body>
</html>

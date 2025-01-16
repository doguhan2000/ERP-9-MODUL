<?php
require_once '../../config/db.php';  // Veritabanı bağlantısını ekle

// Yüklenen dosyaları listele
$upload_dir = '../../uploads/documents/';

// Uploads ana klasörünü oluştur
if (!file_exists('../../uploads')) {
    if (!@mkdir('../../uploads', 0777, true)) {
        die('Uploads klasörü oluşturulamadı. Lütfen manuel olarak oluşturun ve izinleri ayarlayın.');
    }
    chmod('../../uploads', 0777);
}

// Documents klasörünü oluştur
if (!file_exists($upload_dir)) {
    if (!@mkdir($upload_dir, 0777, true)) {
        die('Documents klasörü oluşturulamadı. Lütfen manuel olarak oluşturun ve izinleri ayarlayın.');
    }
    chmod($upload_dir, 0777);
}

// Dosyaları listele
$files = [];
if (is_dir($upload_dir)) {
    $files = array_diff(scandir($upload_dir), array('.', '..'));
}

// Departman dağılımı verilerini al
$department_query = "
    SELECT 
        d.name as department_name,
        COUNT(e.id) as employee_count
    FROM departments d
    LEFT JOIN employees e ON d.id = e.department_id
    WHERE e.status = 'active'
    GROUP BY d.id, d.name";
$department_data = $conn->query($department_query)->fetchAll(PDO::FETCH_ASSOC);

// Maaş dağılımı verilerini al
$salary_query = "
    SELECT 
        CASE
            WHEN salary < 15000 THEN '0-15.000'
            WHEN salary BETWEEN 15000 AND 25000 THEN '15.000-25.000'
            WHEN salary BETWEEN 25000 AND 35000 THEN '25.000-35.000'
            ELSE '35.000+'
        END as salary_range,
        COUNT(*) as count
    FROM employees
    WHERE status = 'active'
    GROUP BY 
        CASE
            WHEN salary < 15000 THEN '0-15.000'
            WHEN salary BETWEEN 15000 AND 25000 THEN '15.000-25.000'
            WHEN salary BETWEEN 25000 AND 35000 THEN '25.000-35.000'
            ELSE '35.000+'
        END";
$salary_data = $conn->query($salary_query)->fetchAll(PDO::FETCH_ASSOC);

// Belge türü dağılımı
$document_type_query = "
    SELECT 
        document_type,
        COUNT(*) as count
    FROM employee_documents
    GROUP BY document_type";
$document_type_data = $conn->query($document_type_query)->fetchAll(PDO::FETCH_ASSOC);

// Departman bazlı ortalama maaş verilerini al (trend_query yerine)
$avg_salary_query = "
    SELECT 
        d.name as department_name,
        ROUND(AVG(e.salary), 2) as avg_salary
    FROM departments d
    LEFT JOIN employees e ON d.id = e.department_id
    WHERE e.status = 'active'
    GROUP BY d.id, d.name
    ORDER BY avg_salary DESC";
$avg_salary_data = $conn->query($avg_salary_query)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belgeler - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h2>Belgeler ve İstatistikler</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                        <i class="bi bi-file-earmark-plus"></i> Yeni Belge Ekle
                    </button>
                </div>

                <!-- Grafik Kartları -->
                <div class="row mb-4">
                    <!-- Departman Dağılımı -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Departmanlara Göre Personel Dağılımı</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Maaş Dağılımı -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Maaş Aralıklarına Göre Dağılım</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salaryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Belge Türü Dağılımı -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Belge Türlerine Göre Dağılım</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="documentTypeChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Departman Bazlı Ortalama Maaş -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Departman Bazlı Ortalama Maaş</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="avgSalaryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Belge Listesi -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Personel</th>
                                <th>Belge Türü</th>
                                <th>Dosya Adı</th>
                                <th>Yükleme Tarihi</th>
                                <th>Açıklama</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $documents_query = "
                                SELECT 
                                    d.*,
                                    e.first_name,
                                    e.last_name,
                                    CASE
                                        WHEN d.document_type = 'is_sozlesmesi' THEN 'İş Sözleşmesi'
                                        WHEN d.document_type = 'saglik_raporu' THEN 'Sağlık Raporu'
                                        WHEN d.document_type = 'diploma' THEN 'Diploma'
                                        WHEN d.document_type = 'sertifika' THEN 'Sertifika'
                                        WHEN d.document_type = 'kimlik' THEN 'Kimlik Belgesi'
                                        WHEN d.document_type = 'adli_sicil' THEN 'Adli Sicil Kaydı'
                                        WHEN d.document_type = 'ikametgah' THEN 'İkametgah'
                                        ELSE 'Diğer'
                                    END as document_type_text
                                FROM employee_documents d
                                JOIN employees e ON d.employee_id = e.id
                                ORDER BY d.upload_date DESC";
                            
                            $documents = $conn->query($documents_query)->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></td>
                                    <td><?= htmlspecialchars($doc['document_type_text']) ?></td>
                                    <td><?= htmlspecialchars(basename($doc['file_path'])) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($doc['upload_date'])) ?></td>
                                    <td><?= htmlspecialchars($doc['notes']) ?></td>
                                    <td>
                                        <a href="<?= $doc['file_path'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteDocument(<?= $doc['id'] ?>)">
                                            <i class="bi bi-trash"></i>
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

    <!-- Belge Ekleme Modalı -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Belge Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="documentForm" action="api/upload_document.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Personel Seçin</label>
                            <select class="form-select" name="employee_id" required>
                                <option value="">Personel seçin...</option>
                                <?php
                                $employees_query = "SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name, last_name";
                                $employees = $conn->query($employees_query)->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['id'] ?>">
                                        <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Belge Türü</label>
                            <select class="form-select" name="document_type" required>
                                <option value="">Belge türü seçin...</option>
                                <option value="is_sozlesmesi">İş Sözleşmesi</option>
                                <option value="saglik_raporu">Sağlık Raporu</option>
                                <option value="diploma">Diploma</option>
                                <option value="sertifika">Sertifika</option>
                                <option value="kimlik">Kimlik Belgesi</option>
                                <option value="adli_sicil">Adli Sicil Kaydı</option>
                                <option value="ikametgah">İkametgah</option>
                                <option value="diger">Diğer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dosya Seç</label>
                            <input type="file" class="form-control" name="document_file" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary">Yükle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteFile(fileName) {
            if (confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
                fetch('api/delete_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ file_name: fileName })
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

        // Form submit olayını engelle ve manuel tetikle
        document.getElementById('documentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            uploadDocument();
        });

        function uploadDocument() {
            const form = document.getElementById('documentForm');
            const formData = new FormData(form);

            fetch('api/upload_document.php', {
                method: 'POST',
                body: formData
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

        // Departman Grafiği
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(departmentCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($department_data, 'department_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($department_data, 'employee_count')) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Maaş Grafiği
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        new Chart(salaryCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($salary_data, 'salary_range')) ?>,
                datasets: [{
                    label: 'Çalışan Sayısı',
                    data: <?= json_encode(array_column($salary_data, 'count')) ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Belge Türü Grafiği
        const documentTypeCtx = document.getElementById('documentTypeChart').getContext('2d');
        new Chart(documentTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($document_type_data, 'document_type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($document_type_data, 'count')) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#4BC0C0', '#FF6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Departman Bazlı Ortalama Maaş Grafiği
        const avgSalaryCtx = document.getElementById('avgSalaryChart').getContext('2d');
        new Chart(avgSalaryCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($avg_salary_data, 'department_name')) ?>,
                datasets: [{
                    label: 'Ortalama Maaş (₺)',
                    data: <?= json_encode(array_column($avg_salary_data, 'avg_salary')) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#4BC0C0', '#FF6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
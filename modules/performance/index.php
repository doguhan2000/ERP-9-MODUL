<?php
require_once '../../config/db.php';

// Personel listesini getir
$employees_query = "
    SELECT 
        e.*,
        d.name as department_name,
        p.title as position_title,
        (
            SELECT AVG(score)
            FROM performance_reviews
            WHERE employee_id = e.id
            AND review_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)
        ) as avg_performance
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    WHERE e.status = 'active'
    ORDER BY e.first_name, e.last_name";

$employees = $conn->query($employees_query)->fetchAll(PDO::FETCH_ASSOC);

// Departmanları getir
$departments_query = "SELECT * FROM departments ORDER BY name";
$departments = $conn->query($departments_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performans Yönetimi - 9ERP</title>
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
        .performance-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .performance-score {
            width: 50px;
            height: 50px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .score-high { background-color: #28a745; }
        .score-medium { background-color: #ffc107; }
        .score-low { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Ana Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include '../includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Performans Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                        <i class="bi bi-plus-lg"></i> Yeni Değerlendirme
                    </button>
                </div>

                <!-- Performans Kartları -->
                <div class="row">
                    <?php foreach ($employees as $employee): ?>
                        <div class="col-md-4 mb-4">
                            <div class="performance-card p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                    </h5>
                                    <?php
                                    $avg_score = round($employee['avg_performance'] ?? 0, 1);
                                    $score_class = $avg_score >= 4 ? 'score-high' : 
                                                ($avg_score >= 3 ? 'score-medium' : 'score-low');
                                    ?>
                                    <div class="performance-score <?= $score_class ?>">
                                        <?= $avg_score ?>
                                    </div>
                                </div>
                                <p class="text-muted mb-2">
                                    <?= htmlspecialchars($employee['department_name']) ?> - 
                                    <?= htmlspecialchars($employee['position_title']) ?>
                                </p>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewPerformance(<?= $employee['id'] ?>)">
                                        Detaylar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Değerlendirme Modal -->
    <div class="modal fade" id="addReviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Performans Değerlendirmesi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <div class="mb-3">
                            <label class="form-label">Personel</label>
                            <select class="form-select" name="employee_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>">
                                        <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Performans Puanı (0-10)</label>
                            <input type="number" class="form-control" name="score" min="0" max="10" step="0.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Değerlendirme</label>
                            <textarea class="form-control" name="review_text" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hedefler</label>
                            <textarea class="form-control" name="goals" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveReview()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveReview() {
            const form = document.getElementById('reviewForm');
            const formData = new FormData(form);

            fetch('api/review.php', {
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
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        function viewPerformance(employeeId) {
            window.location.href = `employee_performance.php?id=${employeeId}`;
        }
    </script>
</body>
</html> 
<?php
require_once '../../config/db.php';

$employee_id = $_GET['id'] ?? 0;

// Çalışan bilgilerini getir
$employee_query = "
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
    WHERE e.id = ?";

$stmt = $conn->prepare($employee_query);
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// Performans değerlendirmelerini getir
$reviews_query = "
    SELECT *
    FROM performance_reviews
    WHERE employee_id = ?
    ORDER BY review_date DESC";

$stmt = $conn->prepare($reviews_query);
$stmt->execute([$employee_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Departmandaki diğer çalışanların performanslarını getir
$department_rankings_query = "
    SELECT 
        e.id,
        e.first_name,
        e.last_name,
        e.position_id,
        p.title as position_title,
        COALESCE(AVG(pr.score), 0) as avg_score,
        COUNT(pr.id) as review_count
    FROM employees e
    LEFT JOIN positions p ON e.position_id = p.id
    LEFT JOIN performance_reviews pr ON e.id = pr.employee_id
    WHERE e.department_id = ? AND e.status = 'active'
    GROUP BY e.id
    ORDER BY avg_score DESC";

$stmt = $conn->prepare($department_rankings_query);
$stmt->execute([$employee['department_id']]);
$department_rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Performans Detayları - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .performance-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .score-high { color: #28a745; }
        .score-medium { color: #ffc107; }
        .score-low { color: #dc3545; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="index.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-left"></i> Geri
                </a>
                <h2 class="d-inline-block mb-0">Personel Performans Detayları</h2>
            </div>
        </div>

        <!-- Personel Bilgileri -->
        <div class="card mb-4">
            <div class="card-body">
                <h3><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></h3>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <p><strong>Departman:</strong> <?= htmlspecialchars($employee['department_name']) ?></p>
                        <p><strong>Pozisyon:</strong> <?= htmlspecialchars($employee['position_title']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>E-posta:</strong> <?= htmlspecialchars($employee['email']) ?></p>
                        <p><strong>Telefon:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>İşe Başlama:</strong> <?= htmlspecialchars($employee['hire_date']) ?></p>
                        <p><strong>Maaş:</strong> <?= number_format($employee['salary'], 2) ?> ₺</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performans Değerlendirmeleri -->
        <h4 class="mb-3">Performans Geçmişi</h4>
        <?php foreach ($reviews as $review): ?>
            <div class="performance-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Değerlendirme Tarihi: <?= date('d.m.Y', strtotime($review['review_date'])) ?></h5>
                    <?php
                    $score = round($review['score'], 1);
                    $score_class = $score >= 7 ? 'score-high' : 
                                 ($score >= 5 ? 'score-medium' : 'score-low');
                    ?>
                    <div class="h4 <?= $score_class ?> mb-0"><?= $score ?>/10</div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Değerlendirme</h6>
                        <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Hedefler</h6>
                        <p><?= nl2br(htmlspecialchars($review['goals'])) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Departman Sıralaması -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Departman Performans Sıralaması</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sıra</th>
                                <th>Personel</th>
                                <th>Pozisyon</th>
                                <th>Ortalama Puan</th>
                                <th>Değerlendirme Sayısı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($department_rankings as $ranking): 
                                $isCurrentEmployee = ($ranking['id'] == $employee_id);
                            ?>
                                <tr <?= $isCurrentEmployee ? 'class="table-primary"' : '' ?>>
                                    <td>
                                        <?php if($rank <= 3): ?>
                                            <i class="bi bi-trophy-fill text-warning"></i>
                                        <?php endif; ?>
                                        <?= $rank ?>
                                    </td>
                                    <td><?= htmlspecialchars($ranking['first_name'] . ' ' . $ranking['last_name']) ?></td>
                                    <td><?= htmlspecialchars($ranking['position_title']) ?></td>
                                    <td>
                                        <?php
                                        $score = round($ranking['avg_score'], 1);
                                        $score_class = $score >= 7 ? 'text-success' : 
                                                     ($score >= 5 ? 'text-warning' : 'text-danger');
                                        ?>
                                        <span class="<?= $score_class ?> fw-bold">
                                            <?= $score ?>/10
                                        </span>
                                    </td>
                                    <td><?= $ranking['review_count'] ?></td>
                                </tr>
                            <?php 
                                $rank++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
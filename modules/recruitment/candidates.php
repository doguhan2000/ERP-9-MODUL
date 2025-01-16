<?php
require_once '../../config/db.php';

$job_id = $_GET['job_id'] ?? 0;

// İş ilanı detaylarını getir
$job_query = "
    SELECT 
        j.*,
        d.name as department_name,
        p.title as position_title
    FROM job_postings j
    LEFT JOIN departments d ON j.department_id = d.id
    LEFT JOIN positions p ON j.position_id = p.id
    WHERE j.id = ?";

$stmt = $conn->prepare($job_query);
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: index.php');
    exit;
}

// Adayları getir
$candidates_query = "
    SELECT * FROM candidates 
    WHERE job_posting_id = ?
    ORDER BY created_at DESC";

$stmt = $conn->prepare($candidates_query);
$stmt->execute([$job_id]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaylar - <?= htmlspecialchars($job['title']) ?> - 9ERP</title>
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
        .candidate-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 20px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        .status-new { background: #e3f2fd; color: #0d47a1; }
        .status-reviewing { background: #fff3e0; color: #e65100; }
        .status-interviewed { background: #e8f5e9; color: #1b5e20; }
        .status-offered { background: #f3e5f5; color: #4a148c; }
        .status-rejected { background: #ffebee; color: #b71c1c; }
        .status-hired { background: #e8f5e9; color: #1b5e20; }
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
                    <a class="nav-link" href="../time/index.php">
                        <i class="bi bi-calendar3"></i> Zaman Yönetimi
                    </a>
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-person-plus"></i> İşe Alım
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-arrow-left"></i> Geri
                        </a>
                        <h2 class="d-inline-block mb-0"><?= htmlspecialchars($job['title']) ?></h2>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                        <i class="bi bi-plus-lg"></i> Yeni Aday
                    </button>
                </div>

                <!-- İş İlanı Detayları -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Departman:</strong> <?= htmlspecialchars($job['department_name']) ?></p>
                                <p class="mb-1"><strong>Pozisyon:</strong> <?= htmlspecialchars($job['position_title']) ?></p>
                                <p class="mb-1"><strong>Tecrübe:</strong> Minimum <?= $job['min_experience'] ?> yıl</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>İngilizce:</strong> 
                                    <?php
                                    $english_levels = [
                                        'beginner' => 'Başlangıç',
                                        'intermediate' => 'Orta',
                                        'advanced' => 'İleri',
                                        'native' => 'Anadil'
                                    ];
                                    echo $english_levels[$job['english_level']];
                                    ?>
                                </p>
                                <p class="mb-1"><strong>Maaş Aralığı:</strong> 
                                    <?= number_format($job['min_salary'], 0, ',', '.') ?> TL - 
                                    <?= number_format($job['max_salary'], 0, ',', '.') ?> TL
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adaylar -->
                <div class="row">
                    <?php foreach ($candidates as $candidate): ?>
                    <div class="col-md-6">
                        <div class="candidate-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <?= htmlspecialchars($candidate['first_name'] . ' ' . $candidate['last_name']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($candidate['email']) ?> | 
                                        <?= htmlspecialchars($candidate['phone']) ?>
                                    </small>
                                </div>
                                <span class="status-badge status-<?= $candidate['status'] ?>">
                                    <?php
                                    $status_text = [
                                        'new' => 'Yeni',
                                        'reviewing' => 'İnceleniyor',
                                        'interviewed' => 'Görüşüldü',
                                        'offered' => 'Teklif Yapıldı',
                                        'rejected' => 'Reddedildi',
                                        'hired' => 'İşe Alındı'
                                    ];
                                    echo $status_text[$candidate['status']];
                                    ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1">
                                    <strong>Tecrübe:</strong> <?= $candidate['experience_years'] ?> yıl
                                </p>
                                <p class="mb-1">
                                    <strong>İngilizce:</strong> <?= $english_levels[$candidate['english_level']] ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Beklenen Maaş:</strong> 
                                    <?= number_format($candidate['expected_salary'], 0, ',', '.') ?> TL
                                </p>
                            </div>
                            <?php if ($candidate['notes']): ?>
                            <div class="mb-3">
                                <strong>Notlar:</strong>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($candidate['notes'])) ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="updateCandidateStatus(<?= $candidate['id'] ?>)">
                                    Durumu Güncelle
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Aday Modalı -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Aday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="candidateForm">
                        <input type="hidden" name="job_posting_id" value="<?= $job_id ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Ad</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Soyad</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefon</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tecrübe (Yıl)</label>
                                <input type="number" class="form-control" name="experience_years" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">İngilizce Seviyesi</label>
                                <select class="form-select" name="english_level" required>
                                    <option value="beginner">Başlangıç</option>
                                    <option value="intermediate">Orta</option>
                                    <option value="advanced">İleri</option>
                                    <option value="native">Anadil</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Beklenen Maaş</label>
                            <input type="number" class="form-control" name="expected_salary" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notlar</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCandidate()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveCandidate() {
            const form = document.getElementById('candidateForm');
            const formData = new FormData(form);

            fetch('api/candidate.php', {
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

        function updateCandidateStatus(candidateId) {
            const status = prompt('Yeni durum (new, reviewing, interviewed, offered, rejected, hired):');
            if (!status) return;

            fetch('api/candidate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${candidateId}&status=${status}`
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

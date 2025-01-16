<?php
require_once '../../config/db.php';

// Departmanları getir
$departments_query = "SELECT * FROM departments ORDER BY name";
$departments = $conn->query($departments_query)->fetchAll(PDO::FETCH_ASSOC);

// Pozisyonları getir
$positions_query = "SELECT * FROM positions ORDER BY title";
$positions = $conn->query($positions_query)->fetchAll(PDO::FETCH_ASSOC);

// Aktif iş ilanlarını getir
$jobs_query = "
    SELECT 
        j.*,
        d.name as department_name,
        p.title as position_title,
        (SELECT COUNT(*) FROM candidates WHERE job_posting_id = j.id) as candidate_count
    FROM job_postings j
    LEFT JOIN departments d ON j.department_id = d.id
    LEFT JOIN positions p ON j.position_id = p.id
    WHERE j.status = 'active'
    ORDER BY j.created_at DESC";
$jobs = $conn->query($jobs_query)->fetchAll(PDO::FETCH_ASSOC);

// İstatistikleri getir
$stats_query = "
    SELECT 
        COUNT(*) as total_jobs,
        (SELECT COUNT(*) FROM candidates) as total_candidates,
        (SELECT COUNT(*) FROM candidates WHERE status = 'new') as new_candidates,
        (SELECT COUNT(*) FROM candidates WHERE status = 'hired') as hired_candidates
    FROM job_postings 
    WHERE status = 'active'";
$stats = $conn->query($stats_query)->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşe Alım - 9ERP</title>
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
        .job-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.2s;
        }
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .job-card .badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        .requirements-list {
            list-style: none;
            padding-left: 0;
        }
        .requirements-list li {
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
        }
        .requirements-list li:before {
            content: "•";
            color: #3498db;
            font-weight: bold;
            position: absolute;
            left: 8px;
        }
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
                    <h2>İşe Alım</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobModal">
                        <i class="bi bi-plus-lg"></i> Yeni İlan
                    </button>
                </div>

                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-primary">
                            <i class="bi bi-briefcase"></i>
                            <h3 class="mb-2"><?= $stats['total_jobs'] ?></h3>
                            <p class="mb-0">Aktif İlan</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-success">
                            <i class="bi bi-people"></i>
                            <h3 class="mb-2"><?= $stats['total_candidates'] ?></h3>
                            <p class="mb-0">Toplam Aday</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-warning">
                            <i class="bi bi-person-plus"></i>
                            <h3 class="mb-2"><?= $stats['new_candidates'] ?></h3>
                            <p class="mb-0">Yeni Başvuru</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-info">
                            <i class="bi bi-person-check"></i>
                            <h3 class="mb-2"><?= $stats['hired_candidates'] ?></h3>
                            <p class="mb-0">İşe Alınan</p>
                        </div>
                    </div>
                </div>

                <!-- İşe Alım Süreci -->
                <div class="col-md-10 p-4">
                    <h2>İşe Alım Süreci</h2>
                    <form id="recruitmentForm">
                        <div class="mb-3">
                            <label class="form-label">Departman</label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deneyim Seviyesi</label>
                            <select class="form-select" name="experience" required>
                                <option value="">Seçiniz</option>
                                <option value="0">Yeni Başlayan</option>
                                <option value="2">Min 2 Yıl</option>
                                <option value="5">Min 5 Yıl</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İngilizce Seviyesi</label>
                            <select class="form-select" name="english_level" required>
                                <option value="beginner">Başlangıç</option>
                                <option value="intermediate">Orta</option>
                                <option value="advanced">İleri</option>
                                <option value="native">Anadil</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="filterCandidates()">Adayları Göster</button>
                    </form>

                    <!-- Aday CV'leri -->
                    <div id="candidateList" class="mt-4">
                        <!-- Filtrelenen adaylar burada gösterilecek -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni İlan Modalı -->
    <div class="modal fade" id="addJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni İş İlanı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="jobForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Departman</label>
                                <select class="form-select" name="department_id" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>">
                                        <?= htmlspecialchars($dept['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pozisyon</label>
                                <select class="form-select" name="position_id" required>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($positions as $pos): ?>
                                    <option value="<?= $pos['id'] ?>">
                                        <?= htmlspecialchars($pos['title']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İlan Başlığı</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İş Tanımı</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gereksinimler</label>
                            <textarea class="form-control" name="requirements" rows="3" required 
                                    placeholder="Her maddeyi yeni satıra yazın"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Minimum Tecrübe (Yıl)</label>
                                <input type="number" class="form-control" name="min_experience" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Minimum Maaş</label>
                                <input type="number" class="form-control" name="min_salary" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Maksimum Maaş</label>
                                <input type="number" class="form-control" name="max_salary" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İngilizce Seviyesi</label>
                            <select class="form-select" name="english_level" required>
                                <option value="beginner">Başlangıç</option>
                                <option value="intermediate">Orta</option>
                                <option value="advanced">İleri</option>
                                <option value="native">Anadil</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveJob()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveJob() {
            const form = document.getElementById('jobForm');
            const formData = new FormData(form);

            fetch('api/job.php', {
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

        function viewCandidates(jobId) {
            window.location.href = `candidates.php?job_id=${jobId}`;
        }

        function filterCandidates() {
            const form = document.getElementById('recruitmentForm');
            const formData = new FormData(form);

            fetch('api/filter_candidates.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const candidateList = document.getElementById('candidateList');
                candidateList.innerHTML = '';

                if (data.success && data.candidates.length > 0) {
                    data.candidates.forEach(candidate => {
                        const candidateDiv = document.createElement('div');
                        candidateDiv.classList.add('candidate-card', 'card', 'mb-3', 'p-3');
                        candidateDiv.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">${candidate.first_name} ${candidate.last_name}</h5>
                                    <p class="mb-1"><strong>Tecrübe:</strong> ${candidate.experience_years} yıl</p>
                                    <p class="mb-2"><strong>İngilizce:</strong> ${
                                        {
                                            'beginner': 'Başlangıç',
                                            'intermediate': 'Orta',
                                            'advanced': 'İleri',
                                            'native': 'Anadil'
                                        }[candidate.english_level] || candidate.english_level
                                    }</p>
                                </div>
                                <a href="uploads/cv/${candidate.cv_file}" 
                                   class="btn btn-primary btn-sm" 
                                   target="_blank">
                                    <i class="bi bi-download"></i> CV İndir
                                </a>
                            </div>
                        `;
                        candidateList.appendChild(candidateDiv);
                    });
                } else {
                    candidateList.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Seçilen kriterlere uygun aday bulunamadı.
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                candidateList.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Bir hata oluştu. Lütfen tekrar deneyin.
                    </div>
                `;
            });
        }
    </script>
</body>
</html>

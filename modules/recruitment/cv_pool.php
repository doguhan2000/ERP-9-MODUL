<?php
require_once '../../config/db.php';

// Departmanları getir
$departments_query = "SELECT * FROM departments ORDER BY name";
$departments = $conn->query($departments_query)->fetchAll(PDO::FETCH_ASSOC);

// Filtreler
$department_id = $_GET['department_id'] ?? '';
$experience = $_GET['experience'] ?? '';
$english_level = $_GET['english_level'] ?? '';
$education_level = $_GET['education_level'] ?? '';

// CV'leri getir
$query = "SELECT 
    cv.*, 
    d.name as department_name,
    GROUP_CONCAT(t.tag_name) as tags
FROM cv_pool cv
LEFT JOIN departments d ON cv.department_id = d.id
LEFT JOIN cv_tags t ON cv.id = t.cv_id
WHERE cv.status = 'active'";

if ($department_id) {
    $query .= " AND cv.department_id = " . intval($department_id);
}
if ($experience) {
    $query .= " AND cv.experience_years >= " . intval($experience);
}
if ($english_level) {
    $query .= " AND cv.english_level = " . $conn->quote($english_level);
}
if ($education_level) {
    $query .= " AND cv.education_level = " . $conn->quote($education_level);
}

$query .= " GROUP BY cv.id ORDER BY cv.created_at DESC";
$cvs = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Havuzu - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
        .cv-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.2s;
        }
        .cv-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .tag {
            display: inline-block;
            padding: 4px 8px;
            margin: 2px;
            background: #e9ecef;
            border-radius: 12px;
            font-size: 0.85em;
        }
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .pdf-preview {
            width: 100%;
            height: 300px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
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
                    <h2>CV Havuzu</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCVModal">
                        <i class="bi bi-plus-lg"></i> Yeni CV Ekle
                    </button>
                </div>

                <!-- Filtreler -->
                <div class="filter-section">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Departman</label>
                            <select class="form-select select2" name="department_id">
                                <option value="">Tümü</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $department_id == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Minimum Tecrübe</label>
                            <select class="form-select" name="experience">
                                <option value="">Tümü</option>
                                <option value="0" <?= $experience === '0' ? 'selected' : '' ?>>0-2 yıl</option>
                                <option value="2" <?= $experience === '2' ? 'selected' : '' ?>>2-5 yıl</option>
                                <option value="5" <?= $experience === '5' ? 'selected' : '' ?>>5+ yıl</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İngilizce Seviyesi</label>
                            <select class="form-select" name="english_level">
                                <option value="">Tümü</option>
                                <option value="beginner" <?= $english_level === 'beginner' ? 'selected' : '' ?>>Başlangıç</option>
                                <option value="intermediate" <?= $english_level === 'intermediate' ? 'selected' : '' ?>>Orta</option>
                                <option value="advanced" <?= $english_level === 'advanced' ? 'selected' : '' ?>>İleri</option>
                                <option value="native" <?= $english_level === 'native' ? 'selected' : '' ?>>Anadil</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Eğitim Seviyesi</label>
                            <select class="form-select" name="education_level">
                                <option value="">Tümü</option>
                                <option value="high_school" <?= $education_level === 'high_school' ? 'selected' : '' ?>>Lise</option>
                                <option value="associate" <?= $education_level === 'associate' ? 'selected' : '' ?>>Ön Lisans</option>
                                <option value="bachelor" <?= $education_level === 'bachelor' ? 'selected' : '' ?>>Lisans</option>
                                <option value="master" <?= $education_level === 'master' ? 'selected' : '' ?>>Yüksek Lisans</option>
                                <option value="phd" <?= $education_level === 'phd' ? 'selected' : '' ?>>Doktora</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- CV Listesi -->
                <div class="row">
                    <?php foreach ($cvs as $cv): ?>
                    <div class="col-md-6">
                        <div class="cv-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">
                                        <?= htmlspecialchars($cv['first_name'] . ' ' . $cv['last_name']) ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($cv['department_name']) ?> | 
                                        <?= $cv['experience_years'] ?> yıl tecrübe
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="viewCV(<?= $cv['id'] ?>)">
                                    CV Görüntüle
                                </button>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1">
                                    <strong>İngilizce:</strong> 
                                    <?php
                                    $english_levels = [
                                        'beginner' => 'Başlangıç',
                                        'intermediate' => 'Orta',
                                        'advanced' => 'İleri',
                                        'native' => 'Anadil'
                                    ];
                                    echo $english_levels[$cv['english_level']];
                                    ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Eğitim:</strong>
                                    <?php
                                    $education_levels = [
                                        'high_school' => 'Lise',
                                        'associate' => 'Ön Lisans',
                                        'bachelor' => 'Lisans',
                                        'master' => 'Yüksek Lisans',
                                        'phd' => 'Doktora'
                                    ];
                                    echo $education_levels[$cv['education_level']];
                                    ?>
                                </p>
                            </div>
                            <?php if ($cv['tags']): ?>
                            <div class="mb-3">
                                <?php foreach (explode(',', $cv['tags']) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="addNote(<?= $cv['id'] ?>)">
                                    Not Ekle
                                </button>
                                <a href="uploads/cv/<?= $cv['cv_file'] ?>" 
                                   class="btn btn-sm btn-outline-success"
                                   download>
                                    <i class="bi bi-download"></i> İndir
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CV Görüntüleme Modalı -->
    <div class="modal fade" id="viewCVModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CV Görüntüle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfPreview" class="pdf-preview"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni CV Modalı -->
    <div class="modal fade" id="addCVModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni CV Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="cvForm" enctype="multipart/form-data">
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
                        <div class="mb-3">
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
                            <label class="form-label">Eğitim Seviyesi</label>
                            <select class="form-select" name="education_level" required>
                                <option value="high_school">Lise</option>
                                <option value="associate">Ön Lisans</option>
                                <option value="bachelor">Lisans</option>
                                <option value="master">Yüksek Lisans</option>
                                <option value="phd">Doktora</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Yetenekler</label>
                            <input type="text" class="form-control" name="skills" 
                                   placeholder="Virgülle ayırarak yazın">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CV (PDF)</label>
                            <input type="file" class="form-control" name="cv_file" 
                                   accept=".pdf" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCV()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Not Ekleme Modalı -->
    <div class="modal fade" id="addNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Not Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="noteForm">
                        <input type="hidden" name="cv_id" id="noteCV">
                        <div class="mb-3">
                            <label class="form-label">Not</label>
                            <textarea class="form-control" name="note_text" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveNote()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Filtre değişikliklerinde formu otomatik gönder
            $('#filterForm select').change(function() {
                $('#filterForm').submit();
            });
        });

        function viewCV(cvId) {
            const modal = new bootstrap.Modal(document.getElementById('viewCVModal'));
            fetch(`api/cv.php?id=${cvId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('pdfPreview').src = 'uploads/cv/' + data.cv_file;
                        modal.show();
                    }
                });
        }

        function saveCV() {
            const form = document.getElementById('cvForm');
            const formData = new FormData(form);

            fetch('api/cv.php', {
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

        function addNote(cvId) {
            document.getElementById('noteCV').value = cvId;
            const modal = new bootstrap.Modal(document.getElementById('addNoteModal'));
            modal.show();
        }

        function saveNote() {
            const form = document.getElementById('noteForm');
            const formData = new FormData(form);

            fetch('api/note.php', {
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
    </script>
</body>
</html>

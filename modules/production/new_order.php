<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['production_logged_in']) || $_SESSION['production_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// İş istasyonlarını getir
$workstations = $conn->query("SELECT * FROM workstations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Proje kodu oluştur (WEB + YIL + AY + Random 3 haneli sayı)
        $project_code = 'WEB' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("
            INSERT INTO production_orders (
                order_code, customer_name, company_name, contact_email, contact_phone,
                project_type, features, tech_stack, start_date, due_date,
                priority, workstation_id, notes, status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending'
            )
        ");

        $stmt->execute([
            $project_code,
            $_POST['customer_name'],
            $_POST['company_name'],
            $_POST['contact_email'],
            $_POST['contact_phone'],
            $_POST['project_type'],
            $_POST['features'],
            $_POST['tech_stack'],
            $_POST['start_date'],
            $_POST['due_date'],
            $_POST['priority'],
            $_POST['workstation_id'],
            $_POST['notes']
        ]);

        $conn->commit();
        header('Location: index.php?success=1');
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Proje Ekle - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Yeni Proje Ekle</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Geri Dön
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Müşteri Bilgileri</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Firma Adı</label>
                                        <input type="text" class="form-control" name="company_name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" class="form-control" name="contact_email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefon</label>
                                        <input type="tel" class="form-control" name="contact_phone" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Proje Detayları</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Proje Tipi</label>
                                        <select class="form-select" name="project_type" required>
                                            <option value="e-commerce">E-ticaret</option>
                                            <option value="corporate">Kurumsal Web Sitesi</option>
                                            <option value="blog">Blog</option>
                                            <option value="custom">Özel Proje</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">İstenen Özellikler</label>
                                        <textarea class="form-control" name="features" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Teknoloji Stack</label>
                                        <textarea class="form-control" name="tech_stack" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Zaman Planlaması</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Başlangıç Tarihi</label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Termin Tarihi</label>
                                        <input type="date" class="form-control" name="due_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Proje Yönetimi</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Öncelik</label>
                                        <select class="form-select" name="priority" required>
                                            <option value="low">Düşük</option>
                                            <option value="medium">Orta</option>
                                            <option value="high">Yüksek</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Departman</label>
                                        <select class="form-select" name="workstation_id" required>
                                            <?php foreach ($workstations as $station): ?>
                                                <option value="<?= $station['id'] ?>"><?= htmlspecialchars($station['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notlar</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Projeyi Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
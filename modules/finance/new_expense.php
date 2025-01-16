<?php
session_start();

if (!isset($_SESSION['finance_logged_in']) || $_SESSION['finance_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../../config/db.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gider Ekle - Finans Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Yeni Gider Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form id="expenseForm" onsubmit="return saveExpense(event)">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gider Türü</label>
                                    <select class="form-select" name="expense_type" required>
                                        <option value="">Seçiniz</option>
                                        <option value="kira">Kira</option>
                                        <option value="fatura">Fatura</option>
                                        <option value="personel">Personel</option>
                                        <option value="malzeme">Malzeme</option>
                                        <option value="diger">Diğer</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tutar (TL)</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tedarikçi/Firma</label>
                                    <input type="text" class="form-control" name="supplier_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vade Tarihi</label>
                                    <input type="date" class="form-control" name="due_date" required 
                                           min="<?= date('Y-m-d') ?>" 
                                           max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Açıklama</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" onclick="history.back()">İptal</button>
                                <button type="submit" class="btn btn-danger">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveExpense(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('expenseForm'));
            
            fetch('api/save_expense.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Gider başarıyla kaydedildi.');
                    window.location.href = 'index.php';
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu.');
            });

            return false;
        }
    </script>
</body>
</html> 
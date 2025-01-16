<?php
require_once '../../config/db.php';

// Hesap planını getir
$accounts = $conn->query("
    SELECT * FROM account_chart 
    WHERE type IN ('expense', 'liability')
    ORDER BY code ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Fiş Oluştur - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Yeni Fiş Oluştur</h5>
                    </div>
                    <div class="card-body">
                        <form id="voucherForm">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fiş Türü</label>
                                    <select class="form-select" name="voucher_type" required>
                                        <option value="">Seçiniz</option>
                                        <option value="rent">Kira Fişi</option>
                                        <option value="tax">Vergi Fişi</option>
                                        <option value="insurance">Sigorta Fişi</option>
                                        <option value="utility">Fatura Fişi</option>
                                        <option value="other">Diğer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fiş Tarihi</label>
                                    <input type="date" class="form-control" name="voucher_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tutar</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Açıklama</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" onclick="history.back()">İptal</button>
                                <button type="button" class="btn btn-primary" onclick="saveVoucher()">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveVoucher() {
            const form = document.getElementById('voucherForm');
            const formData = new FormData(form);

            fetch('api/save_voucher.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Fiş başarıyla kaydedildi.');
                    window.location.href = 'index.php';
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu: ' + error.message);
            });
        }
    </script>
</body>
</html> 
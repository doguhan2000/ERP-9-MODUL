<?php
require_once '../../config/db.php';

// Son 5 muhasebe fişi
$recent_vouchers = $conn->query("
    SELECT 
        v.*,
        u.username as created_by_name
    FROM accounting_vouchers v
    LEFT JOIN users u ON v.created_by = u.id
    ORDER BY v.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Aylık gider dağılımını doğrudan muhasebe fişlerinden al
$current_month = date('n');
$current_year = date('Y');

$monthly_expenses_query = $conn->prepare("
    SELECT 
        CASE type
            WHEN 'rent' THEN 'Kira Giderleri'
            WHEN 'tax' THEN 'Vergi Giderleri'
            WHEN 'insurance' THEN 'Sigorta Giderleri'
            WHEN 'utility' THEN 'Fatura Giderleri'
            WHEN 'other' THEN 'Diğer Giderler'
        END as expense_type,
        SUM(total_amount) as total_amount
    FROM accounting_vouchers 
    WHERE MONTH(voucher_date) = ? 
    AND YEAR(voucher_date) = ?
    AND status = 'posted'
    AND type != 'salary'  -- Personel giderlerini hariç tut
    GROUP BY type
");

$monthly_expenses_query->execute([$current_month, $current_year]);
$monthly_expenses = $monthly_expenses_query->fetchAll(PDO::FETCH_ASSOC);

// Personel giderlerini bordro tablosundan al
$salary_query = $conn->prepare("
    SELECT COALESCE(SUM(net_salary), 0) as total_salary
    FROM payrolls
    WHERE period_month = ? 
    AND period_year = ? 
    AND payment_status != 'cancelled'
");

$salary_query->execute([$current_month, $current_year]);
$salary_data = $salary_query->fetch(PDO::FETCH_ASSOC);

// Personel giderlerini ekle (eğer varsa)
if ($salary_data['total_salary'] > 0) {
    $monthly_expenses[] = [
        'expense_type' => 'Personel Giderleri',
        'total_amount' => $salary_data['total_salary']
    ];
}

// Sıfır değerli gider türlerini ekle
$all_expense_types = [
    'Kira Giderleri',
    'Vergi Giderleri',
    'Sigorta Giderleri',
    'Fatura Giderleri',
    'Diğer Giderler'
];

$existing_types = array_column($monthly_expenses, 'expense_type');

foreach ($all_expense_types as $type) {
    if (!in_array($type, $existing_types)) {
        $monthly_expenses[] = [
            'expense_type' => $type,
            'total_amount' => 0
        ];
    }
}

// Grafik renkleri
$chart_colors = [
    'Personel Giderleri' => '#FF6384',
    'Kira Giderleri' => '#36A2EB',
    'Fatura Giderleri' => '#FFCE56',
    'Vergi Giderleri' => '#4BC0C0',
    'Sigorta Giderleri' => '#9966FF',
    'Diğer Giderler' => '#FF9F40'
];

// Toplam personel giderini hesapla (üst karttaki değer için)
$total_salary = $salary_data['total_salary'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muhasebe Yönetimi - 9ERP</title>
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
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Muhasebe Yönetimi</h2>
                    <button class="btn btn-primary" onclick="location.href='new_voucher.php'">
                        <i class="bi bi-plus-lg"></i> Yeni Fiş Oluştur
                    </button>
                </div>

                <!-- İstatistik Kartları -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Aylık Personel Giderleri</h6>
                                <h3><?= number_format($salary_data['total_salary'], 2, ',', '.') ?> ₺</h3>
                            </div>
                        </div>
                    </div>
                    <!-- Diğer istatistik kartları buraya eklenebilir -->
                </div>

                <!-- Grafikler ve Fişler -->
                <div class="row mb-4">
                    <!-- Sol taraf: Personel Giderleri Grafiği -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Personel Giderleri</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salaryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ taraf: Son Muhasebe Fişleri -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Son Muhasebe Fişleri</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fiş No</th>
                                                <th>Tarih</th>
                                                <th>Tutar</th>
                                                <th>Durum</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_vouchers as $voucher): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($voucher['voucher_no']) ?></td>
                                                    <td><?= date('d.m.Y', strtotime($voucher['voucher_date'])) ?></td>
                                                    <td><?= number_format($voucher['total_amount'], 2, ',', '.') ?> ₺</td>
                                                    <td>
                                                        <span class="badge bg-<?= $voucher['status'] == 'posted' ? 'success' : 
                                                            ($voucher['status'] == 'draft' ? 'warning' : 'danger') ?>">
                                                            <?= $voucher['status'] == 'posted' ? 'Onaylandı' : 
                                                                ($voucher['status'] == 'draft' ? 'Taslak' : 'İptal Edildi') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="view_voucher.php?id=<?= $voucher['id'] ?>" 
                                                               class="btn btn-sm btn-info" 
                                                               title="Görüntüle">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="edit_voucher.php?id=<?= $voucher['id'] ?>" 
                                                               class="btn btn-sm btn-warning" 
                                                               title="Düzenle">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    onclick="deleteVoucher(<?= $voucher['id'] ?>)"
                                                                    title="Sil">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Personel giderleri için çubuk grafik
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        new Chart(salaryCtx, {
            type: 'bar',
            data: {
                labels: ['Personel Giderleri'],
                datasets: [{
                    label: 'Tutar (₺)',
                    data: [<?= $salary_data['total_salary'] ?>],
                    backgroundColor: '#FF6384'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY'
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        function deleteVoucher(id) {
            if (confirm('Bu fişi silmek istediğinizden emin misiniz?')) {
                fetch('api/delete_voucher.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Fiş başarıyla silindi.');
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu.');
                });
            }
        }
    </script>
</body>
</html> 
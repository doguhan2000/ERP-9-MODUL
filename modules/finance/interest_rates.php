<?php
session_start();

if (!isset($_SESSION['finance_logged_in']) || $_SESSION['finance_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../../config/db.php';

// Toplam geliri getir
$total_income = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM incomes 
    WHERE status = 'pending'
")->fetch(PDO::FETCH_ASSOC)['total'];

// Sabit faiz oranları
$interest_rates['result'] = [
    ['bank' => 'Ziraat Bankası', 'rate' => '45.00', 'creditType' => 'İhtiyaç Kredisi'],
    ['bank' => 'İş Bankası', 'rate' => '42.50', 'creditType' => 'Ticari Kredi'],
    ['bank' => 'Garanti BBVA', 'rate' => '40.00', 'creditType' => 'Konut Kredisi'],
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faiz Oranları - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <style>
        .interest-card {
            background: linear-gradient(135deg, #1a2a6c, #2a4858);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .interest-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2ecc71;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .interest-title {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .calculation {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .calculation strong {
            color: #2ecc71;
        }
        .total-income {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .total-income h3 {
            margin: 0;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
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
                <h2 class="mb-4">Faiz Oranları</h2>

                <!-- Toplam Gelir Gösterimi -->
                <div class="total-income mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Toplam Gelir</p>
                            <h3><?= number_format($total_income, 2, ',', '.') ?> ₺</h3>
                        </div>
                        <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.8;"></i>
                    </div>
                </div>
                
                <div class="row">
                    <?php foreach ($interest_rates['result'] as $rate): ?>
                        <div class="col-md-4">
                            <div class="interest-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="interest-title">
                                        <?= htmlspecialchars($rate['bank']) ?>
                                        <div class="small opacity-75"><?= htmlspecialchars($rate['creditType']) ?></div>
                                    </div>
                                    <div class="interest-value">
                                        %<?= htmlspecialchars($rate['rate']) ?>
                                    </div>
                                </div>
                                <div class="calculation">
                                    <p class="mb-2">Yatırılacak Tutar: <strong><?= number_format($total_income, 2, ',', '.') ?> ₺</strong></p>
                                    <p class="mb-0">1 Yıllık Tahmini Kazanç: <strong><?= number_format($total_income * ($rate['rate']/100), 2, ',', '.') ?> ₺</strong></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

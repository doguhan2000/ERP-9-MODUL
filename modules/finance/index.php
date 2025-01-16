<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['finance_logged_in']) || $_SESSION['finance_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../../config/db.php';

// Canlı kur bilgilerini al
function getCurrencyRates() {
    $apiUrl = "https://hasanadiguzel.com.tr/api/kurgetir";
    $response = file_get_contents($apiUrl);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    return null;
}

$currency_rates = getCurrencyRates();

// Banka hesaplarını getir
$bank_accounts = $conn->query("
    SELECT * FROM bank_accounts 
    WHERE status = 'active'
    ORDER BY currency, account_name
")->fetchAll(PDO::FETCH_ASSOC);

// Gelirleri getir
$incomes = $conn->query("
    SELECT * FROM incomes 
    WHERE status = 'pending' 
    ORDER BY due_date ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Giderleri getir
$expenses = $conn->query("
    SELECT * FROM payments 
    WHERE status = 'pending' 
    ORDER BY due_date ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finans Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
    <style>
        .currency-ticker {
            background: linear-gradient(135deg, #1a2a6c, #2a4858);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .currency-item {
            display: inline-block;
            margin: 0 25px;
            padding: 10px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            backdrop-filter: blur(5px);
            transition: transform 0.3s ease;
        }
        .currency-item:hover {
            transform: translateY(-3px);
        }
        .currency-name {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 3px;
        }
        .currency-value {
            color: #2ecc71;
            font-size: 1.2rem;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .update-time {
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
            background: rgba(0,0,0,0.2);
            padding: 5px 10px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Canlı Kur Bilgileri -->
        <div class="row">
            <div class="col-12">
                <div class="currency-ticker position-relative">
                    <?php 
                    if ($currency_rates && isset($currency_rates['TCMB_AnlikKurBilgileri'])): 
                        foreach ($currency_rates['TCMB_AnlikKurBilgileri'] as $currency): 
                            if (in_array($currency['Isim'], ['ABD DOLARI', 'EURO', 'İNGİLİZ STERLİNİ', 'KANADA DOLARI'])): 
                    ?>
                        <div class="currency-item">
                            <div class="currency-name">
                                <?= htmlspecialchars($currency['Isim']) ?>
                            </div>
                            <div class="currency-value">
                                <?= number_format((float)$currency['ForexSelling'], 4) ?> ₺
                            </div>
                        </div>
                    <?php 
                            endif;
                        endforeach;
                    endif;

                    // BTC bilgisi için CoinGecko API
                    $btcApiUrl = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=try";
                    $btcResponse = file_get_contents($btcApiUrl);
                    if ($btcResponse !== false) {
                        $btcData = json_decode($btcResponse, true);
                        if (isset($btcData['bitcoin']['try'])):
                    ?>
                        <div class="currency-item">
                            <div class="currency-name">Bitcoin (BTC)</div>
                            <div class="currency-value">
                                <?= number_format((float)$btcData['bitcoin']['try'], 2) ?> ₺
                            </div>
                        </div>
                    <?php 
                        endif;
                    }
                    ?>
                    <div class="update-time">
                        <i class="bi bi-clock"></i> 
                        <?= date('H:i:s') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Finans Yönetimi</h2>
                    <div>
                        <button class="btn btn-danger me-2" onclick="location.href='new_expense.php'">
                            <i class="bi bi-dash-circle"></i> Gider Ekle
                        </button>
                        <button class="btn btn-success" onclick="location.href='new_income.php'">
                            <i class="bi bi-plus-circle"></i> Gelir Ekle
                        </button>
                    </div>
                </div>

                <!-- Banka Hesapları -->
                <div class="row mb-4">
                    <?php foreach ($bank_accounts as $account): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($account['bank_name']) ?></h6>
                                    <h5 class="card-title"><?= htmlspecialchars($account['account_name']) ?></h5>
                                    <p class="card-text h4">
                                        <?= number_format($account['current_balance'], 2, ',', '.') ?> 
                                        <?= $account['currency'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Gelirler ve Giderler -->
                <div class="row">
                    <!-- Gelirler -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Gelirler</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Müşteri</th>
                                                <th>Tür</th>
                                                <th>Tutar</th>
                                                <th>Tarih</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($incomes as $income): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($income['customer_name']) ?></td>
                                                    <td><?= ucfirst($income['income_type']) ?></td>
                                                    <td><?= number_format($income['amount'], 2, ',', '.') ?> ₺</td>
                                                    <td><?= date('d.m.Y', strtotime($income['due_date'])) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-warning me-2">Bekliyor</span>
                                                            <button class="btn btn-sm btn-danger" onclick="deleteIncome(<?= $income['id'] ?>)">
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

                    <!-- Giderler -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Giderler</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tedarikçi</th>
                                                <th>Tür</th>
                                                <th>Tutar</th>
                                                <th>Tarih</th>
                                                <th>Durum</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expenses as $expense): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($expense['supplier_name']) ?></td>
                                                    <td><?= ucfirst($expense['payment_type']) ?></td>
                                                    <td><?= number_format($expense['amount'], 2, ',', '.') ?> ₺</td>
                                                    <td><?= date('d.m.Y', strtotime($expense['due_date'])) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-warning me-2">Bekliyor</span>
                                                            <button class="btn btn-sm btn-danger" onclick="deleteExpense(<?= $expense['id'] ?>)">
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
        // Her 5 dakikada bir sayfayı yenile (kur güncellemesi için)
        setTimeout(function() {
            location.reload();
        }, 300000);

        function deleteIncome(id) {
            if (confirm('Bu geliri silmek istediğinizden emin misiniz?')) {
                fetch('api/delete_income.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                });
            }
        }

        function deleteExpense(id) {
            if (confirm('Bu gideri silmek istediğinizden emin misiniz?')) {
                fetch('api/delete_expense.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>

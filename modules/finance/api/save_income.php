<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['amount']) || !isset($_POST['income_type']) || !isset($_POST['customer_name']) || !isset($_POST['due_date'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $amount = floatval($_POST['amount']);
    $income_type = $_POST['income_type'];
    $customer_name = $_POST['customer_name'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'] ?? '';

    // Güncel kurları al
    $apiUrl = "https://hasanadiguzel.com.tr/api/kurgetir";
    $response = file_get_contents($apiUrl);
    $currency_data = json_decode($response, true);

    if (!$currency_data || !isset($currency_data['TCMB_AnlikKurBilgileri'])) {
        throw new Exception('Kur bilgileri alınamadı.');
    }

    // USD ve EUR kurlarını bul
    $usd_rate = 0;
    $eur_rate = 0;
    foreach ($currency_data['TCMB_AnlikKurBilgileri'] as $currency) {
        if ($currency['Isim'] === 'ABD DOLARI') {
            $usd_rate = floatval($currency['ForexSelling']);
        } elseif ($currency['Isim'] === 'EURO') {
            $eur_rate = floatval($currency['ForexSelling']);
        }
    }

    // Döviz hesaplamaları
    $usd_amount = $amount / $usd_rate;
    $eur_amount = $amount / $eur_rate;

    // Transaction başlat
    $conn->beginTransaction();

    // Geliri kaydet
    $query = "INSERT INTO incomes (income_type, amount, customer_name, due_date, description, status, created_at) 
              VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([$income_type, $amount, $customer_name, $due_date, $description]);

    // Banka hesaplarını güncelle
    // TL hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'Ziraat Bankası' AND currency = 'TRY'");
    $stmt->execute([$amount]);

    // USD hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'İş Bankası' AND currency = 'USD'");
    $stmt->execute([$usd_amount]);

    // EUR hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'Garanti' AND currency = 'EUR'");
    $stmt->execute([$eur_amount]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Gelir başarıyla kaydedildi.'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
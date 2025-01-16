<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['amount']) || !isset($_POST['expense_type']) || !isset($_POST['supplier_name']) || !isset($_POST['due_date'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $amount = floatval($_POST['amount']);
    $expense_type = $_POST['expense_type'];
    $supplier_name = $_POST['supplier_name'];
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

    // Gideri kaydet
    $query = "INSERT INTO payments (payment_type, amount, supplier_name, due_date, description, status, created_at) 
              VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([$expense_type, $amount, $supplier_name, $due_date, $description]);

    // TL hesabından düş
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE bank_name = 'Ziraat Bankası' AND currency = 'TRY'");
    $stmt->execute([$amount]);

    // USD hesabından düş
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE bank_name = 'İş Bankası' AND currency = 'USD'");
    $stmt->execute([$usd_amount]);

    // EUR hesabından düş
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance - ? WHERE bank_name = 'Garanti' AND currency = 'EUR'");
    $stmt->execute([$eur_amount]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Gider başarıyla kaydedildi.'
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
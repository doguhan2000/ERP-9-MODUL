<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID parametresi eksik.');
    }

    $id = $data['id'];

    // Önce gider bilgilerini al
    $stmt = $conn->prepare("SELECT amount FROM payments WHERE id = ?");
    $stmt->execute([$id]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expense) {
        throw new Exception('Gider bulunamadı.');
    }

    $amount = $expense['amount'];

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

    // Gideri sil
    $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$id]);

    // Banka hesaplarına geri ekle
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
        'message' => 'Gider başarıyla silindi.'
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
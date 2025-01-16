<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['customer_id']) || empty($data['items'])) {
        throw new Exception('Gerekli parametreler eksik.');
    }

    // Müşteri bilgilerini al
    $stmt = $conn->prepare("
        SELECT c.name, c.company_name 
        FROM customers c 
        WHERE c.id = ?
    ");
    $stmt->execute([$data['customer_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    $customer_name = $customer['company_name'] ?: $customer['name'];

    // Güncel kurları al
    $apiUrl = "https://hasanadiguzel.com.tr/api/kurgetir";
    $response = @file_get_contents($apiUrl);
    if ($response === false) {
        throw new Exception('Kur bilgileri alınamadı. Varsayılan kurlar kullanılacak.');
    }
    
    $currency_data = json_decode($response, true);
    
    // USD ve EUR kurlarını bul (varsayılan değerler)
    $usd_rate = 35.00;
    $eur_rate = 36.00;
    
    if ($currency_data && isset($currency_data['TCMB_AnlikKurBilgileri'])) {
        foreach ($currency_data['TCMB_AnlikKurBilgileri'] as $currency) {
            if ($currency['Isim'] === 'ABD DOLARI') {
                $usd_rate = floatval($currency['ForexSelling']);
            } elseif ($currency['Isim'] === 'EURO') {
                $eur_rate = floatval($currency['ForexSelling']);
            }
        }
    }

    $conn->beginTransaction();

    // Satış kaydı oluştur
    $stmt = $conn->prepare("
        INSERT INTO sales (
            customer_id,
            total_amount,
            discount_rate,
            final_amount,
            status
        ) VALUES (?, ?, ?, ?, 'completed')
    ");

    $subtotal = 0;
    foreach ($data['items'] as $item) {
        $subtotal += $item['sale_price'] * $item['quantity'];
    }

    $discount = $subtotal * ($data['discount_rate'] / 100);
    $total = $subtotal - $discount;

    $stmt->execute([
        $data['customer_id'],
        $subtotal,
        $data['discount_rate'],
        $total
    ]);

    $saleId = $conn->lastInsertId();

    // Satış detaylarını kaydet ve stok güncelle
    $stmt = $conn->prepare("
        INSERT INTO sale_items (
            sale_id,
            inventory_item_id,
            quantity,
            unit_price,
            purchase_price,
            total_price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $updateStock = $conn->prepare("
        UPDATE inventory_items 
        SET quantity = quantity - ? 
        WHERE id = ? AND quantity >= ?
    ");

    foreach ($data['items'] as $item) {
        // Ürünün alış fiyatını al
        $stmt = $conn->prepare("SELECT purchase_price FROM inventory_items WHERE id = ?");
        $stmt->execute([$item['id']]);
        $purchase_price = $stmt->fetchColumn();

        // Satış detayını kaydet
        $stmt = $conn->prepare("
            INSERT INTO sale_items (
                sale_id, inventory_item_id, quantity, 
                unit_price, purchase_price, total_price
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $total_price = $item['quantity'] * $item['sale_price'];
        
        $stmt->execute([
            $saleId,
            $item['id'],
            $item['quantity'],
            $item['sale_price'],
            $purchase_price, // Alış fiyatını kaydediyoruz
            $total_price
        ]);

        // Stok güncelle
        $affected = $updateStock->execute([
            $item['quantity'],
            $item['id'],
            $item['quantity']
        ]);

        if ($updateStock->rowCount() === 0) {
            throw new Exception('Stok miktarı yetersiz.');
        }
    }

    // Kârı hesapla
    $total_cost = 0;
    foreach ($data['items'] as $item) {
        $stmt = $conn->prepare("SELECT purchase_price FROM inventory_items WHERE id = ?");
        $stmt->execute([$item['id']]);
        $purchase_price = $stmt->fetchColumn();
        $total_cost += ($purchase_price * $item['quantity']);
    }
    
    $profit = $total - $total_cost;

    // Kârı farklı para birimlerine çevir
    $profit_usd = $profit / $usd_rate;
    $profit_eur = $profit / $eur_rate;

    // Banka hesaplarını güncelle
    // TL hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'Ziraat Bankası' AND currency = 'TRY'");
    $stmt->execute([$profit]);

    // USD hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'İş Bankası' AND currency = 'USD'");
    $stmt->execute([$profit_usd]);

    // EUR hesabı
    $stmt = $conn->prepare("UPDATE bank_accounts SET current_balance = current_balance + ? WHERE bank_name = 'Garanti' AND currency = 'EUR'");
    $stmt->execute([$profit_eur]);

    // Gelir kaydı oluştur
    $stmt = $conn->prepare("
        INSERT INTO incomes (
            income_type, 
            amount, 
            customer_name, 
            description, 
            status,
            due_date,
            created_at
        ) VALUES (?, ?, ?, ?, 'collected', NOW(), NOW())
    ");

    $stmt->execute([
        'satis',
        $total,
        $customer_name,
        'Satış - Satış No: ' . $saleId
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Satış başarıyla tamamlandı.',
        'sale_id' => $saleId
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
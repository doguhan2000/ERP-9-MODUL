<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['item_id']) || !isset($_POST['supplier_product_id'])) {
        throw new Exception('Gerekli parametreler eksik.');
    }

    $conn->beginTransaction();

    // Tedarikçi ürün bilgilerini al
    $stmt = $conn->prepare("
        SELECT sp.*, i.quantity, s.id as supplier_id 
        FROM supplier_products sp
        JOIN inventory_items i ON sp.inventory_item_id = i.id
        JOIN suppliers s ON sp.supplier_id = s.id
        WHERE sp.id = ? AND i.id = ?
    ");
    $stmt->execute([
        $_POST['supplier_product_id'],
        $_POST['item_id']
    ]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Ürün bulunamadı.');
    }

    // Stok miktarını güncelle
    $stmt = $conn->prepare("
        UPDATE inventory_items 
        SET quantity = quantity + 1,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$_POST['item_id']]);

    // Satın alma işlemini kaydet
    $stmt = $conn->prepare("
        INSERT INTO purchase_transactions 
        (inventory_item_id, supplier_id, quantity, price, currency) 
        VALUES (?, ?, 1, ?, ?)
    ");
    $stmt->execute([
        $_POST['item_id'],
        $product['supplier_id'],
        $product['price'],
        $product['currency']
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Ürün başarıyla satın alındı.'
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
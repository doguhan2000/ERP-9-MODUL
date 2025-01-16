<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Satış ID gerekli.');
    }

    $stmt = $conn->prepare("
        SELECT 
            si.*,
            i.item_code,
            i.name,
            i.purchase_price
        FROM sale_items si
        JOIN inventory_items i ON si.inventory_item_id = i.id
        WHERE si.sale_id = ?
        ORDER BY si.id ASC
    ");
    
    $stmt->execute([$_GET['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        throw new Exception('Satış detayları bulunamadı.');
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
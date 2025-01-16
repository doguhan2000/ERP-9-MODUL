<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Müşteri ID gerekli.');
    }

    $stmt = $conn->prepare("
        SELECT 
            c.*,
            cg.name as group_name,
            COUNT(s.id) as total_sales,
            SUM(s.final_amount) as total_revenue,
            SUM(s.final_amount - (
                SELECT SUM(si.quantity * i.purchase_price)
                FROM sale_items si
                JOIN inventory_items i ON si.inventory_item_id = i.id
                WHERE si.sale_id = s.id
            )) as total_profit
        FROM customers c
        LEFT JOIN customer_groups cg ON c.group_id = cg.id
        LEFT JOIN sales s ON c.id = s.customer_id
        WHERE c.id = ? AND c.status = 'active'
        GROUP BY c.id
    ");
    
    $stmt->execute([$_GET['id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception('Müşteri bulunamadı.');
    }

    echo json_encode([
        'success' => true,
        'customer' => $customer
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
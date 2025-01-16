<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Görev ID gerekli.');
    }

    $stmt = $conn->prepare("
        SELECT 
            t.*,
            c.name as customer_name,
            c.company_name
        FROM tasks t
        LEFT JOIN customers c ON t.customer_id = c.id
        WHERE t.id = ? AND t.status != 'deleted'
    ");
    
    $stmt->execute([$_GET['id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception('Görev bulunamadı.');
    }

    echo json_encode([
        'success' => true,
        'task' => $task
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
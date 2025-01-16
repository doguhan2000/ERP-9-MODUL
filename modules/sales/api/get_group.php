<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Grup ID gerekli.');
    }

    $stmt = $conn->prepare("
        SELECT * FROM customer_groups 
        WHERE id = ? AND status = 'active'
    ");
    
    $stmt->execute([$_GET['id']]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        throw new Exception('Grup bulunamadı.');
    }

    echo json_encode([
        'success' => true,
        'group' => $group
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
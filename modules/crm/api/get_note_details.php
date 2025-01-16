<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Not ID gerekli.');
    }

    $stmt = $conn->prepare("
        SELECT 
            n.*,
            c.name as customer_name,
            c.company_name
        FROM notes n
        LEFT JOIN customers c ON n.customer_id = c.id
        WHERE n.id = ? AND n.status = 'active'
    ");
    
    $stmt->execute([$_GET['id']]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note) {
        throw new Exception('Not bulunamadı.');
    }

    echo json_encode([
        'success' => true,
        'note' => $note
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
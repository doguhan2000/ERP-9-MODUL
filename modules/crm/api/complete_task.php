<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['task_id'])) {
        throw new Exception('Görev ID gerekli.');
    }

    $stmt = $conn->prepare("
        UPDATE tasks 
        SET status = 'completed'
        WHERE id = ? AND status != 'deleted'
    ");

    $stmt->execute([$data['task_id']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Görev bulunamadı veya güncelleme başarısız.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Görev tamamlandı olarak işaretlendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
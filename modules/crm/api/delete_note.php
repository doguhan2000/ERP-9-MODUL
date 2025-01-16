<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['note_id'])) {
        throw new Exception('Not ID gerekli.');
    }

    $stmt = $conn->prepare("
        UPDATE notes 
        SET status = 'deleted'
        WHERE id = ? AND status = 'active'
    ");

    $stmt->execute([$data['note_id']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Not bulunamadı veya silme başarısız.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Not başarıyla silindi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
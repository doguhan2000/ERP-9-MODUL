<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['note_id']) || !isset($_POST['title']) || !isset($_POST['content']) || !isset($_POST['type'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        UPDATE notes 
        SET title = ?, content = ?, type = ?
        WHERE id = ? AND status = 'active'
    ");

    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        $_POST['type'],
        $_POST['note_id']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Not bulunamadı veya güncelleme başarısız.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Not başarıyla güncellendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
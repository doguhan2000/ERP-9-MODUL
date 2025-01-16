<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('Ürün ID\'si gerekli.');
    }

    $stmt = $conn->prepare("UPDATE inventory_items SET status = 'passive' WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);

    echo json_encode([
        'success' => true,
        'message' => 'Ürün başarıyla silindi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['group_id']) || !isset($_POST['discount_rate'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    // İndirim oranını kontrol et
    $discount_rate = floatval($_POST['discount_rate']);
    if ($discount_rate < 0 || $discount_rate > 100) {
        throw new Exception('İndirim oranı 0-100 arasında olmalıdır.');
    }

    $stmt = $conn->prepare("
        UPDATE customer_groups 
        SET discount_rate = ?
        WHERE id = ? AND status = 'active'
    ");

    $stmt->execute([
        $discount_rate,
        $_POST['group_id']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Müşteri grubu bulunamadı veya güncelleme başarısız.');
    }

    echo json_encode([
        'success' => true,
        'message' => 'İndirim oranı başarıyla güncellendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
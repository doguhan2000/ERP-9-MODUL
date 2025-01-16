<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['project_id']) || !isset($_POST['status'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        UPDATE production_orders 
        SET status = ?, progress = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['status'],
        $_POST['progress'],
        $_POST['project_id']
    ]);

    // Durum güncellemesini kaydet
    $stmt = $conn->prepare("
        INSERT INTO project_status_logs (
            project_id, status, progress, notes, created_at
        ) VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_POST['project_id'],
        $_POST['status'],
        $_POST['progress'],
        $_POST['notes']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Proje durumu güncellendi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
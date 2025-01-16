<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['project_id']) || !isset($_POST['progress'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        UPDATE production_orders 
        SET progress = ?,
            status = CASE 
                WHEN ? = 100 THEN 'completed'
                WHEN ? > 0 THEN 'in_progress'
                ELSE status 
            END,
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['progress'],
        $_POST['progress'],
        $_POST['progress'],
        $_POST['project_id']
    ]);

    // İlerleme güncellemesini kaydet
    $stmt = $conn->prepare("
        INSERT INTO project_progress_logs (
            project_id, progress, notes, created_at
        ) VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_POST['project_id'],
        $_POST['progress'],
        $_POST['notes']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Proje ilerlemesi güncellendi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
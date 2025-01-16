<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $employee_id = $_POST['employee_id'] ?? '';
        $score = $_POST['score'] ?? '';
        $review_text = $_POST['review_text'] ?? '';
        $goals = $_POST['goals'] ?? '';

        if (!$employee_id || !$score || !$review_text || !$goals) {
            throw new Exception('Tüm alanları doldurunuz.');
        }

        $query = "INSERT INTO performance_reviews 
                 (employee_id, score, review_text, goals, review_date) 
                 VALUES (?, ?, ?, ?, CURRENT_DATE)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$employee_id, $score, $review_text, $goals]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek'
    ]);
} 
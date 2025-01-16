<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "INSERT INTO job_postings (
            department_id, position_id, title, description, requirements,
            min_experience, min_salary, max_salary, english_level
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $_POST['department_id'],
            $_POST['position_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['requirements'],
            $_POST['min_experience'],
            $_POST['min_salary'],
            $_POST['max_salary'],
            $_POST['english_level']
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

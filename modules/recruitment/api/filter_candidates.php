<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Örnek adaylar (test için)
        $sample_candidates = [
            [
                'first_name' => 'Ahmet',
                'last_name' => 'Yılmaz',
                'experience_years' => 3,
                'english_level' => 'intermediate',
                'cv_file' => 'ornek_cv1.pdf'
            ],
            [
                'first_name' => 'Ayşe',
                'last_name' => 'Demir',
                'experience_years' => 5,
                'english_level' => 'advanced',
                'cv_file' => 'ornek_cv2.pdf'
            ],
            [
                'first_name' => 'Mehmet',
                'last_name' => 'Kaya',
                'experience_years' => 2,
                'english_level' => 'beginner',
                'cv_file' => 'ornek_cv3.pdf'
            ]
        ];

        // Filtreleme kriterleri
        $department_id = $_POST['department_id'] ?? '';
        $experience = intval($_POST['experience'] ?? 0);
        $english_level = $_POST['english_level'] ?? '';

        // Filtreleme
        $filtered_candidates = array_filter($sample_candidates, function($candidate) use ($experience, $english_level) {
            return $candidate['experience_years'] >= $experience &&
                   (!$english_level || $candidate['english_level'] === $english_level);
        });

        echo json_encode([
            'success' => true, 
            'candidates' => array_values($filtered_candidates)
        ]);

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
<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // CV dosyasını yükle
        $cv_file = $_FILES['cv_file'];
        $file_ext = strtolower(pathinfo($cv_file['name'], PATHINFO_EXTENSION));
        
        if ($file_ext !== 'pdf') {
            throw new Exception('Sadece PDF dosyaları kabul edilmektedir.');
        }

        $upload_dir = '../uploads/cv/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = uniqid() . '.pdf';
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($cv_file['tmp_name'], $file_path)) {
            throw new Exception('Dosya yüklenirken bir hata oluştu.');
        }

        // CV bilgilerini veritabanına kaydet
        $query = "INSERT INTO cv_pool (
            first_name, last_name, email, phone, department_id,
            experience_years, english_level, education_level,
            skills, cv_file
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['department_id'],
            $_POST['experience_years'],
            $_POST['english_level'],
            $_POST['education_level'],
            $_POST['skills'],
            $file_name
        ]);

        $cv_id = $conn->lastInsertId();

        // Etiketleri ekle
        if (!empty($_POST['skills'])) {
            $skills = explode(',', $_POST['skills']);
            $tag_query = "INSERT INTO cv_tags (cv_id, tag_name) VALUES (?, ?)";
            $tag_stmt = $conn->prepare($tag_query);

            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (!empty($skill)) {
                    $tag_stmt->execute([$cv_id, $skill]);
                }
            }
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $query = "SELECT cv_file FROM cv_pool WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_GET['id']]);
        $cv = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cv) {
            echo json_encode(['success' => true, 'cv_file' => $cv['cv_file']]);
        } else {
            throw new Exception('CV bulunamadı.');
        }
    } catch (Exception $e) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

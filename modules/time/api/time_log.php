<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $employee_id = $_POST['employee_id'];
        $type = isset($_POST['check_in']) ? 'check_in' : 'check_out';
        $time = isset($_POST['check_in']) ? $_POST['check_in'] : $_POST['check_out'];
        $date = date('Y-m-d', strtotime($time));

        // Mevcut kaydı kontrol et
        $check_query = "SELECT * FROM time_logs WHERE employee_id = ? AND DATE(check_in) = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([$employee_id, $date]);
        $existing_log = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_log) {
            // Mevcut kaydı güncelle
            $update_query = "UPDATE time_logs SET $type = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->execute([$time, $existing_log['id']]);

            // Eğer hem giriş hem çıkış varsa toplam süreyi ve durumu hesapla
            if ($type === 'check_out' || $existing_log['check_out']) {
                $log_query = "SELECT * FROM time_logs WHERE id = ?";
                $stmt = $conn->prepare($log_query);
                $stmt->execute([$existing_log['id']]);
                $log = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($log['check_in'] && $log['check_out']) {
                    $check_in_time = strtotime($log['check_in']);
                    $check_out_time = strtotime($log['check_out']);
                    $total_hours = ($check_out_time - $check_in_time) / 3600;

                    // Durumu belirle
                    $status = 'normal';
                    $check_in_hour = date('H', $check_in_time);
                    $check_out_hour = date('H', $check_out_time);

                    if ($check_in_hour >= 9) {
                        $status = 'late';
                    } elseif ($check_out_hour < 17) {
                        $status = 'early_leave';
                    }

                    $update_query = "UPDATE time_logs SET total_hours = ?, status = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->execute([$total_hours, $status, $existing_log['id']]);
                }
            }
        } else {
            // Yeni kayıt oluştur
            if ($type === 'check_in') {
                $status = date('H', strtotime($time)) >= 9 ? 'late' : 'normal';
                $query = "INSERT INTO time_logs (employee_id, check_in, status) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([$employee_id, $time, $status]);
            }
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

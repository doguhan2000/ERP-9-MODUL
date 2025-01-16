<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// JSON verilerini al
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // İzin talebi oluştur
            $employee_id = 1; // TODO: Giriş yapmış kullanıcının ID'si

            // Tarih aralığını kontrol et
            $start_date = new DateTime($data['start_date']);
            $end_date = new DateTime($data['end_date']);
            $interval = $start_date->diff($end_date);
            $total_days = $interval->days + 1;

            if ($total_days <= 0) {
                throw new Exception('Geçersiz tarih aralığı');
            }

            // İzin hakkını kontrol et
            $stmt = $conn->prepare("
                SELECT * FROM leave_types WHERE id = ?
            ");
            $stmt->execute([$data['leave_type_id']]);
            $leave_type = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$leave_type) {
                throw new Exception('Geçersiz izin türü');
            }

            if ($leave_type['max_days']) {
                // Bu yıl kullanılan izin günlerini hesapla
                $stmt = $conn->prepare("
                    SELECT COALESCE(SUM(total_days), 0) as used_days
                    FROM employee_leaves
                    WHERE employee_id = ?
                    AND leave_type_id = ?
                    AND YEAR(start_date) = YEAR(CURRENT_DATE)
                    AND status = 'approved'
                ");
                $stmt->execute([$employee_id, $data['leave_type_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (($result['used_days'] + $total_days) > $leave_type['max_days']) {
                    throw new Exception('Yeterli izin hakkınız bulunmamaktadır');
                }
            }

            // İzin talebini kaydet
            $stmt = $conn->prepare("
                INSERT INTO employee_leaves 
                (employee_id, leave_type_id, start_date, end_date, total_days, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $employee_id,
                $data['leave_type_id'],
                $data['start_date'],
                $data['end_date'],
                $total_days,
                $data['reason']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'İzin talebi oluşturuldu'
            ]);
            break;

        case 'PATCH':
            // İzin talebini onayla/reddet
            if (!isset($data['id']) || !isset($data['action'])) {
                throw new Exception('Geçersiz istek');
            }

            $approved_by = 1; // TODO: Giriş yapmış kullanıcının ID'si

            if ($data['action'] === 'approve') {
                $status = 'approved';
            } elseif ($data['action'] === 'reject') {
                $status = 'rejected';
            } else {
                throw new Exception('Geçersiz işlem');
            }

            $stmt = $conn->prepare("
                UPDATE employee_leaves 
                SET status = ?,
                    approved_by = ?,
                    approved_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$status, $approved_by, $data['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'İzin talebi güncellendi'
            ]);
            break;

        default:
            throw new Exception('Geçersiz istek metodu');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

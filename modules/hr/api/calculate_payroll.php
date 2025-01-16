<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $employee_id = $data['employee_id'];
        $month = $data['month'];
        $year = $data['year'];

        // Çalışan bilgilerini al
        $stmt = $conn->prepare("SELECT salary FROM employees WHERE id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        $base_salary = $employee['salary'];
        
        // Kesintileri hesapla
        $sgk_employee = $base_salary * 0.14; // %14 SGK çalışan payı
        $unemployment = $base_salary * 0.01; // %1 İşsizlik sigortası
        $stamp_tax = $base_salary * 0.00759; // Damga vergisi
        
        // Gelir vergisi dilimleri (2024 yılı için örnek)
        $tax_rate = 0.15; // Basitleştirilmiş vergi oranı
        $income_tax = ($base_salary - $sgk_employee - $unemployment) * $tax_rate;

        // Net maaşı hesapla
        $net_salary = $base_salary - $sgk_employee - $unemployment - $income_tax - $stamp_tax;

        // Bordro kaydını oluştur/güncelle
        $query = "INSERT INTO payrolls 
                 (employee_id, period_month, period_year, base_salary, gross_salary,
                  income_tax, stamp_tax, insurance_deduction, unemployment_insurance, net_salary)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                 base_salary = VALUES(base_salary),
                 gross_salary = VALUES(gross_salary),
                 income_tax = VALUES(income_tax),
                 stamp_tax = VALUES(stamp_tax),
                 insurance_deduction = VALUES(insurance_deduction),
                 unemployment_insurance = VALUES(unemployment_insurance),
                 net_salary = VALUES(net_salary)";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $employee_id, $month, $year, $base_salary, $base_salary,
            $income_tax, $stamp_tax, $sgk_employee, $unemployment, $net_salary
        ]);

        echo json_encode([
            'success' => true,
            'data' => [
                'gross_salary' => $base_salary,
                'net_salary' => $net_salary,
                'deductions' => [
                    'sgk' => $sgk_employee,
                    'unemployment' => $unemployment,
                    'income_tax' => $income_tax,
                    'stamp_tax' => $stamp_tax
                ]
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} 
<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['employee_no']) || !isset($_POST['first_name']) || !isset($_POST['last_name']) || 
        !isset($_POST['email']) || !isset($_POST['department_id']) || !isset($_POST['position_id']) || 
        !isset($_POST['salary'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    // Personel bilgilerini kaydet
    $employee_query = "INSERT INTO employees (
        employee_no, first_name, last_name, email, 
        department_id, position_id, salary, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";

    $stmt = $conn->prepare($employee_query);
    $stmt->execute([
        $_POST['employee_no'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['department_id'],
        $_POST['position_id'],
        $_POST['salary']
    ]);

    $last_id = $conn->lastInsertId();

    // Bordro kaydı oluştur
    $current_month = date('n');
    $current_year = date('Y');
    
    // Maaş hesaplamaları
    $base_salary = $_POST['salary'];
    $meal_allowance = 500;
    $transport_allowance = 300;
    $gross_salary = $base_salary + $meal_allowance + $transport_allowance;
    
    // Kesintiler
    $income_tax = $gross_salary * 0.15;
    $stamp_tax = $gross_salary * 0.00759;
    $insurance_deduction = $gross_salary * 0.14;
    $unemployment_insurance = $gross_salary * 0.01;
    
    // Net maaş
    $net_salary = $gross_salary - ($income_tax + $stamp_tax + $insurance_deduction + $unemployment_insurance);

    $payroll_query = "INSERT INTO payrolls (
        employee_id, period_month, period_year,
        base_salary, overtime_hours, overtime_payment,
        bonus, meal_allowance, transport_allowance,
        gross_salary, income_tax, stamp_tax,
        insurance_deduction, unemployment_insurance, net_salary,
        payment_status, payment_date
    ) VALUES (
        :employee_id, :period_month, :period_year,
        :base_salary, 0, 0,
        0, :meal_allowance, :transport_allowance,
        :gross_salary, :income_tax, :stamp_tax,
        :insurance_deduction, :unemployment_insurance, :net_salary,
        'pending', CURRENT_DATE
    )";

    $stmt = $conn->prepare($payroll_query);
    $stmt->execute([
        'employee_id' => $last_id,
        'period_month' => $current_month,
        'period_year' => $current_year,
        'base_salary' => $base_salary,
        'meal_allowance' => $meal_allowance,
        'transport_allowance' => $transport_allowance,
        'gross_salary' => $gross_salary,
        'income_tax' => $income_tax,
        'stamp_tax' => $stamp_tax,
        'insurance_deduction' => $insurance_deduction,
        'unemployment_insurance' => $unemployment_insurance,
        'net_salary' => $net_salary
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Personel ve bordro kaydı başarıyla oluşturuldu.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
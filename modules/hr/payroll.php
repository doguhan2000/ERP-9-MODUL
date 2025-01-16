<?php
require_once '../../config/db.php';

// Aktif personelleri getir
$employees_query = "
    SELECT 
        e.*,
        d.name as department_name,
        p.title as position_title
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN positions p ON e.position_id = p.id
    WHERE e.status = 'active'
    ORDER BY e.first_name, e.last_name";

$employees = $conn->query($employees_query)->fetchAll(PDO::FETCH_ASSOC);

// Seçilen ay ve yıl (varsayılan olarak içinde bulunduğumuz ay)
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordro Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <div class="nav flex-column">
                    <!-- Mevcut menü öğeleri -->
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Bordro Yönetimi</h2>
                    <div>
                        <select class="form-select d-inline-block w-auto me-2" id="periodSelect">
                            <?php
                            for($i = 1; $i <= 12; $i++) {
                                $selected = $i == $selected_month ? 'selected' : '';
                                echo "<option value='$i' $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                            }
                            ?>
                        </select>
                        <select class="form-select d-inline-block w-auto me-2" id="yearSelect">
                            <?php
                            $current_year = date('Y');
                            for($i = $current_year - 2; $i <= $current_year + 1; $i++) {
                                $selected = $i == $selected_year ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                        <button class="btn btn-primary" onclick="calculateAllPayrolls()">
                            <i class="bi bi-calculator"></i> Toplu Hesapla
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Personel</th>
                                <th>Departman</th>
                                <th>Pozisyon</th>
                                <th>Brüt Maaş</th>
                                <th>Net Maaş</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                                <td><?= htmlspecialchars($emp['department_name']) ?></td>
                                <td><?= htmlspecialchars($emp['position_title']) ?></td>
                                <td><?= number_format($emp['salary'], 2, ',', '.') ?> ₺</td>
                                <td id="net_<?= $emp['id'] ?>">-</td>
                                <td id="status_<?= $emp['id'] ?>">
                                    <span class="badge bg-secondary">Hesaplanmadı</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="calculatePayroll(<?= $emp['id'] ?>)">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="viewPayslip(<?= $emp['id'] ?>)">
                                        <i class="bi bi-file-text"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calculatePayroll(employeeId) {
            const month = document.getElementById('periodSelect').value;
            const year = document.getElementById('yearSelect').value;

            fetch('api/calculate_payroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    employee_id: employeeId,
                    month: month,
                    year: year
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById(`net_${employeeId}`).textContent = 
                        result.data.net_salary.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
                    document.getElementById(`status_${employeeId}`).innerHTML = 
                        '<span class="badge bg-success">Hesaplandı</span>';
                }
            });
        }

        function calculateAllPayrolls() {
            const employees = <?= json_encode(array_column($employees, 'id')) ?>;
            employees.forEach(id => calculatePayroll(id));
        }

        function viewPayslip(employeeId) {
            const month = document.getElementById('periodSelect').value;
            const year = document.getElementById('yearSelect').value;
            window.open(`payslip.php?employee_id=${employeeId}&month=${month}&year=${year}`, '_blank');
        }
    </script>
</body>
</html> 
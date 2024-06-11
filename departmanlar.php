<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departmanlar</title>
    <link rel="stylesheet" href="hr.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 50%;
            margin: auto;
        }
        .table-container {
            width: 80%;
            margin: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="container">
        <aside id="sidebar">
            <button id="menu-toggle"><i class="fas fa-bars"></i></button>
            <h2>Menü</h2>
            <ul>
                <li><a href="calisanlar.php">Çalışanlar</a></li>
                <li><a href="departmanlar.php">Departmanlar</a></li>
                <li><a href="raporlar.php">Raporlar</a></li>
            </ul>
        </aside>
        <main id="main-content">
            <button onclick="history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Geri</button>
            <section id="departments" class="content-section active">
                <h2>Departmanlar Yüzdelikleri</h2>
                <div class="chart-container">
                    <canvas id="departmentChart"></canvas>
                </div>
                <h2>Maaş Aralıkları</h2>
                <div class="chart-container">
                    <canvas id="salaryChart"></canvas>
                </div>
                <div class="table-container">
                    <table id="salaryTable">
                        <thead>
                            <tr>
                                <th>Maaş Aralığı</th>
                                <th>Çalışan Sayısı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dinamik olarak doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctxDepartment = document.getElementById('departmentChart').getContext('2d');
            const ctxSalary = document.getElementById('salaryChart').getContext('2d');
            fetch('departman_veri.php')
                .then(response => response.json())
                .then(data => {
                    const departmentNames = Object.keys(data.departments);
                    const departmentCounts = Object.values(data.departments);
                    const salaryData = data.salaryRanges.map(s => s.count);
                    const salaryLabels = data.salaryRanges.map(s => s.range);

                    new Chart(ctxDepartment, {
                        type: 'pie',
                        data: {
                            labels: departmentNames,
                            datasets: [{
                                label: 'Departman Yüzdelikleri',
                                data: departmentCounts,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Departman Yüzdelikleri'
                                }
                            }
                        }
                    });

                    new Chart(ctxSalary, {
                        type: 'bar',
                        data: {
                            labels: salaryLabels,
                            datasets: [{
                                label: 'Çalışan Sayısı',
                                data: salaryData,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Maaş Aralıkları'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    const salaryTableBody = document.querySelector('#salaryTable tbody');
                    data.salaryRanges.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${row.range}</td><td>${row.count}</td>`;
                        salaryTableBody.appendChild(tr);
                    });
                });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar</title>
    <link rel="stylesheet" href="hr.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 50%;
            margin: auto;
            margin-top: 20px;
        }
        .table-container {
            width: 80%;
            margin: auto;
            margin-top: 20px;
        }
        .content-section {
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
            <section id="performance" class="content-section">
                <h2>Çalışan Performans Raporu</h2>
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
                <div class="table-container">
                    <table id="performanceTable">
                        <thead>
                            <tr>
                                <th>Çalışan Adı</th>
                                <th>Performans Puanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dinamik olarak doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </section>
            <section id="attendance" class="content-section">
                <h2>Çalışan Devamlılık Raporu</h2>
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div class="table-container">
                    <table id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Çalışan Adı</th>
                                <th>Tarih</th>
                                <th>Durum</th>
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
            // Performance Chart
            const ctxPerformance = document.getElementById('performanceChart').getContext('2d');
            fetch('performans_veri.php')
                .then(response => response.json())
                .then(data => {
                    const employeeNames = data.map(item => item.name);
                    const performanceScores
                    const performanceScores = data.map(item => item.score);

new Chart(ctxPerformance, {
    type: 'bar',
    data: {
        labels: employeeNames,
        datasets: [{
            label: 'Performans Puanı',
            data: performanceScores,
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
                text: 'Çalışan Performans Puanları'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

const performanceTableBody = document.querySelector('#performanceTable tbody');
data.forEach(row => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${row.name}</td><td>${row.score}</td>`;
    performanceTableBody.appendChild(tr);
});
});

// Attendance Chart
const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
fetch('devamlilik_veri.php')
.then(response => response.json())
.then(data => {
const attendanceStatuses = data.reduce((acc, item) => {
    if (!acc[item.status]) {
        acc[item.status] = 0;
    }
    acc[item.status]++;
    return acc;
}, {});

const statusLabels = Object.keys(attendanceStatuses);
const statusCounts = Object.values(attendanceStatuses);

new Chart(ctxAttendance, {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{
            label: 'Devamlılık Durumu',
            data: statusCounts,
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
                text: 'Çalışan Devamlılık Durumu'
            }
        }
    }
});

const attendanceTableBody = document.querySelector('#attendanceTable tbody');
data.forEach(row => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${row.name}</td><td>${row.date}</td><td>${row.status}</td>`;
    attendanceTableBody.appendChild(tr);
});
});
});
</script>
</body>
</html>

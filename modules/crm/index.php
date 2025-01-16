<?php
require_once '../../config/db.php';

// Müşterileri getir
$customers = $conn->query("
    SELECT 
        c.*,
        cg.name as group_name,
        COUNT(s.id) as total_sales,
        SUM(s.final_amount) as total_revenue,
        SUM(s.final_amount - (
            SELECT SUM(si.quantity * i.purchase_price)
            FROM sale_items si
            JOIN inventory_items i ON si.inventory_item_id = i.id
            WHERE si.sale_id = s.id
        )) as total_profit
    FROM customers c
    LEFT JOIN customer_groups cg ON c.group_id = cg.id
    LEFT JOIN sales s ON c.id = s.customer_id
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY total_profit DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri İlişkileri Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Müşteri İlişkileri Yönetimi</h2>
                    <div>
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                            <i class="bi bi-plus-circle"></i> Yeni Görev
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newNoteModal">
                            <i class="bi bi-journal-plus"></i> Yeni Not
                        </button>
                    </div>
                </div>

                <!-- Müşteri Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Müşteri</th>
                                        <th>Grup</th>
                                        <th>Toplam Satış</th>
                                        <th>Toplam Kâr</th>
                                        <th>Son İletişim</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($customer['name']) ?></strong>
                                            <?php if ($customer['company_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($customer['company_name']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($customer['group_name']) ?></td>
                                        <td><?= $customer['total_sales'] ?></td>
                                        <td class="<?= $customer['total_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($customer['total_profit'], 2) ?> TL
                                        </td>
                                        <td>-</td>
                                        <td>
                                            <span class="badge bg-success">Aktif</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewCustomerDetails(<?= $customer['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="addNote(<?= $customer['id'] ?>)">
                                                <i class="bi bi-journal-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="addTask(<?= $customer['id'] ?>)">
                                                <i class="bi bi-calendar-plus"></i>
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
        </div>
    </div>

    <!-- Müşteri Detay Modal -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Müşteri Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerDetailsContent">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Temel Bilgiler</h6>
                            <div id="customerBasicInfo"></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Satış Özeti</h6>
                            <div id="customerSalesInfo"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewCustomerDetails(customerId) {
        fetch(`api/get_customer_details.php?id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Temel bilgiler
                    let basicInfo = `
                        <p><strong>Müşteri Adı:</strong> ${data.customer.name}</p>
                        <p><strong>Firma:</strong> ${data.customer.company_name || '-'}</p>
                        <p><strong>Telefon:</strong> ${data.customer.phone || '-'}</p>
                        <p><strong>E-posta:</strong> ${data.customer.email || '-'}</p>
                        <p><strong>Grup:</strong> ${data.customer.group_name}</p>
                        <p><strong>Adres:</strong> ${data.customer.address || '-'}</p>
                    `;
                    
                    // Satış özeti
                    let salesInfo = `
                        <p><strong>Toplam Satış:</strong> ${data.customer.total_sales}</p>
                        <p><strong>Toplam Gelir:</strong> ${Number(data.customer.total_revenue).toFixed(2)} TL</p>
                        <p><strong>Toplam Kâr:</strong> 
                            <span class="${data.customer.total_profit >= 0 ? 'text-success' : 'text-danger'}">
                                ${Number(data.customer.total_profit).toFixed(2)} TL
                            </span>
                        </p>
                    `;

                    document.getElementById('customerBasicInfo').innerHTML = basicInfo;
                    document.getElementById('customerSalesInfo').innerHTML = salesInfo;
                    
                    new bootstrap.Modal(document.getElementById('customerDetailsModal')).show();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
    }
    </script>
</body>
</html> 
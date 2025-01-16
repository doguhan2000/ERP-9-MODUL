<?php
require_once '../../config/db.php';

// Müşteri gruplarını getir
$groups = $conn->query("SELECT * FROM customer_groups WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

// Müşterileri getir
$customers = $conn->query("
    SELECT 
        c.*,
        cg.name as group_name,
        cg.discount_rate
    FROM customers c
    LEFT JOIN customer_groups cg ON c.group_id = cg.id
    WHERE c.status = 'active'
    ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Yönetimi - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 main-sidebar">
                <?php include __DIR__ . '/includes/sidebar.php'; ?>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Müşteri Yönetimi</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                        <i class="bi bi-plus-circle"></i> Yeni Müşteri
                    </button>
                </div>

                <!-- Müşteri Listesi -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Müşteri Adı</th>
                                        <th>Firma Adı</th>
                                        <th>Grup</th>
                                        <th>İndirim Oranı</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($customer['name']) ?></td>
                                        <td><?= htmlspecialchars($customer['company_name'] ?? '-') ?></td>
                                        <td>
                                            <?= htmlspecialchars($customer['group_name']) ?>
                                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="editGroup(<?= $customer['group_id'] ?>)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td>
                                            %<?= number_format($customer['discount_rate'], 2) ?>
                                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="editDiscount(<?= $customer['group_id'] ?>, '<?= htmlspecialchars($customer['group_name']) ?>', <?= $customer['discount_rate'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                                        <td><?= htmlspecialchars($customer['email']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editCustomer(<?= htmlspecialchars(json_encode($customer)) ?>)">
                                                <i class="bi bi-pencil"></i> Düzenle
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="startSale(<?= $customer['id'] ?>)">
                                                <i class="bi bi-cart"></i> Satış Yap
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Yeni Müşteri Modal -->
                <div class="modal fade" id="newCustomerModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Yeni Müşteri Ekle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="newCustomerForm">
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Grubu</label>
                                        <select name="group_id" class="form-select" required>
                                            <?php foreach ($groups as $group): ?>
                                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Firma Adı</label>
                                        <input type="text" name="company_name" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefon</label>
                                        <input type="tel" name="phone" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Adres</label>
                                        <textarea name="address" class="form-control"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="button" class="btn btn-primary" onclick="saveCustomer()">Kaydet</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Müşteri Düzenleme Modal -->
                <div class="modal fade" id="editCustomerModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Müşteri Düzenle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editCustomerForm">
                                    <input type="hidden" name="customer_id" id="edit_customer_id">
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Adı</label>
                                        <input type="text" class="form-control" name="name" id="edit_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Firma Adı</label>
                                        <input type="text" class="form-control" name="company_name" id="edit_company_name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Grubu</label>
                                        <select class="form-select" name="group_id" id="edit_group_id" required>
                                            <?php foreach ($groups as $group): ?>
                                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefon</label>
                                        <input type="tel" class="form-control" name="phone" id="edit_phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" class="form-control" name="email" id="edit_email">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Adres</label>
                                        <textarea class="form-control" name="address" id="edit_address"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="button" class="btn btn-primary" onclick="updateCustomer()">Güncelle</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- İndirim Oranı Düzenleme Modal -->
                <div class="modal fade" id="editDiscountModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">İndirim Oranı Düzenle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editDiscountForm">
                                    <input type="hidden" name="group_id" id="discount_group_id">
                                    <div class="mb-3">
                                        <label class="form-label">Müşteri Grubu</label>
                                        <input type="text" class="form-control" id="discount_group_name" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">İndirim Oranı (%)</label>
                                        <input type="number" class="form-control" name="discount_rate" id="discount_rate" 
                                               min="0" max="100" step="0.01" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="button" class="btn btn-primary" onclick="updateDiscount()">Güncelle</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function saveCustomer() {
        const form = document.getElementById('newCustomerForm');
        const formData = new FormData(form);

        fetch('api/save_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Müşteri başarıyla kaydedildi.');
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }

    function startSale(customerId) {
        window.location.href = `new_sale.php?customer_id=${customerId}`;
    }

    function editCustomer(customer) {
        document.getElementById('edit_customer_id').value = customer.id;
        document.getElementById('edit_name').value = customer.name;
        document.getElementById('edit_company_name').value = customer.company_name || '';
        document.getElementById('edit_group_id').value = customer.group_id;
        document.getElementById('edit_phone').value = customer.phone || '';
        document.getElementById('edit_email').value = customer.email || '';
        document.getElementById('edit_address').value = customer.address || '';
        
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    }

    function updateCustomer() {
        const form = document.getElementById('editCustomerForm');
        const formData = new FormData(form);

        fetch('api/update_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Müşteri başarıyla güncellendi.');
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }

    function editDiscount(groupId, groupName, discountRate) {
        document.getElementById('discount_group_id').value = groupId;
        document.getElementById('discount_group_name').value = groupName;
        document.getElementById('discount_rate').value = discountRate;
        
        new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
    }

    function updateDiscount() {
        const form = document.getElementById('editDiscountForm');
        const formData = new FormData(form);

        fetch('api/update_discount.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('İndirim oranı başarıyla güncellendi.');
                location.reload();
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
<?php
require_once '../../config/db.php';

// Hesap planını getir
$accounts = $conn->query("
    SELECT * FROM account_chart 
    ORDER BY code ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Planı - 9ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Hesap Planı</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAccountModal">
                        <i class="bi bi-plus-lg"></i> Yeni Hesap Ekle
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Hesap Kodu</th>
                                        <th>Hesap Adı</th>
                                        <th>Tür</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accounts as $account): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($account['code']) ?></td>
                                        <td><?= htmlspecialchars($account['name']) ?></td>
                                        <td>
                                            <?php
                                            switch($account['type']) {
                                                case 'asset':
                                                    echo 'Varlık';
                                                    break;
                                                case 'liability':
                                                    echo 'Borç';
                                                    break;
                                                case 'equity':
                                                    echo 'Özkaynak';
                                                    break;
                                                case 'income':
                                                    echo 'Gelir';
                                                    break;
                                                case 'expense':
                                                    echo 'Gider';
                                                    break;
                                                default:
                                                    echo $account['type'];
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning" 
                                                        onclick="editAccount(<?= $account['id'] ?>, '<?= $account['code'] ?>', '<?= $account['name'] ?>', '<?= $account['type'] ?>')"
                                                        title="Düzenle">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="deleteAccount(<?= $account['id'] ?>)"
                                                        title="Sil">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
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

    <!-- Yeni Hesap Modalı -->
    <div class="modal fade" id="newAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Hesap Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newAccountForm">
                        <div class="mb-3">
                            <label class="form-label">Hesap Kodu</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hesap Adı</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hesap Türü</label>
                            <select class="form-select" name="type" required>
                                <option value="asset">Varlık</option>
                                <option value="liability">Borç</option>
                                <option value="equity">Özkaynak</option>
                                <option value="income">Gelir</option>
                                <option value="expense">Gider</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewAccount()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveNewAccount() {
            // AJAX ile yeni hesap kaydetme işlemi
            const form = document.getElementById('newAccountForm');
            const formData = new FormData(form);

            fetch('api/save_account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function editAccount(id, code, name, type) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_type').value = type;
            
            new bootstrap.Modal(document.getElementById('editAccountModal')).show();
        }

        function updateAccount() {
            const form = document.getElementById('editAccountForm');
            const formData = new FormData(form);

            fetch('api/update_account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function deleteAccount(id) {
            if (confirm('Bu hesabı silmek istediğinizden emin misiniz?')) {
                fetch('api/delete_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
    </script>
</body>
</html> 
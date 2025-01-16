<?php
require_once '../../config/db.php';

if (!isset($_GET['customer_id'])) {
    header('Location: customers.php');
    exit;
}

// Müşteri bilgilerini getir
$stmt = $conn->prepare("
    SELECT 
        c.*,
        cg.name as group_name,
        cg.discount_rate
    FROM customers c
    LEFT JOIN customer_groups cg ON c.group_id = cg.id
    WHERE c.id = ? AND c.status = 'active'
");
$stmt->execute([$_GET['customer_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header('Location: customers.php');
    exit;
}

// Stok ürünlerini getir
$products = $conn->query("
    SELECT 
        i.*,
        c.name as category_name
    FROM inventory_items i
    JOIN inventory_categories c ON i.category_id = c.id
    WHERE i.status = 'active' AND i.quantity > 0
    ORDER BY c.name, i.name
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Satış - 9ERP</title>
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
                    <h2>Yeni Satış</h2>
                    <a href="customers.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Geri
                    </a>
                </div>

                <!-- Müşteri Bilgileri -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Müşteri Bilgileri</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Müşteri:</strong> <?= htmlspecialchars($customer['name']) ?></p>
                                <p><strong>Firma:</strong> <?= htmlspecialchars($customer['company_name'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Grup:</strong> <?= htmlspecialchars($customer['group_name']) ?></p>
                                <p><strong>İndirim Oranı:</strong> %<?= number_format($customer['discount_rate'], 2) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Telefon:</strong> <?= htmlspecialchars($customer['phone'] ?? '-') ?></p>
                                <p><strong>E-posta:</strong> <?= htmlspecialchars($customer['email'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Ürün Listesi -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Ürünler</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Ürün Kodu</th>
                                                <th>Ürün Adı</th>
                                                <th>Kategori</th>
                                                <th>Stok</th>
                                                <th>Fiyat</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?= $product['item_code'] ?></td>
                                                <td><?= $product['name'] ?></td>
                                                <td><?= $product['category_name'] ?></td>
                                                <td><?= $product['quantity'] ?></td>
                                                <td><?= number_format($product['sale_price'], 2) ?> TL</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?= htmlspecialchars(json_encode($product)) ?>)">
                                                        <i class="bi bi-cart-plus"></i> Sepete Ekle
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

                    <!-- Sepet -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Sepet</h5>
                                <div id="cart"></div>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Ara Toplam:</span>
                                    <span id="subtotal">0.00 TL</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>İndirim (<?= $customer['discount_rate'] ?>%):</span>
                                    <span id="discount">0.00 TL</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Genel Toplam:</strong>
                                    <strong id="total">0.00 TL</strong>
                                </div>
                                <button class="btn btn-success w-100" onclick="completeSale()">
                                    <i class="bi bi-check-circle"></i> Satışı Tamamla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const customerId = <?= $customer['id'] ?>;
    const discountRate = <?= $customer['discount_rate'] ?>;
    let cart = [];

    function addToCart(product) {
        // Üründen zaten var mı kontrol et
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            // Stok kontrolü
            if (existingItem.quantity >= product.quantity) {
                alert('Yeterli stok yok!');
                return;
            }
            existingItem.quantity++;
        } else {
            // Yeni ürün ekle
            cart.push({
                id: product.id,
                name: product.name,
                sale_price: product.sale_price,
                quantity: 1
            });
        }
        
        updateCart();
    }

    function updateQuantity(productId, change) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            const newQuantity = item.quantity + change;
            if (newQuantity > 0) {
                // Stok kontrolü
                const product = <?= json_encode($products) ?>.find(p => p.id === productId);
                if (newQuantity <= product.quantity) {
                    item.quantity = newQuantity;
                } else {
                    alert('Yeterli stok yok!');
                    return;
                }
            } else {
                removeFromCart(productId);
                return;
            }
        }
        updateCart();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        updateCart();
    }

    function updateCart() {
        const cartDiv = document.getElementById('cart');
        let html = '';
        let subtotal = 0;

        cart.forEach(item => {
            subtotal += item.sale_price * item.quantity;
            html += `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">${item.sale_price} TL x ${item.quantity}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="btn-group me-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <button class="btn btn-sm btn-outline-secondary">${item.quantity}</button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        const discount = subtotal * (discountRate / 100);
        const total = subtotal - discount;

        cartDiv.innerHTML = html;
        document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' TL';
        document.getElementById('discount').textContent = discount.toFixed(2) + ' TL';
        document.getElementById('total').textContent = total.toFixed(2) + ' TL';
    }

    function completeSale() {
        if (cart.length === 0) {
            alert('Sepet boş!');
            return;
        }

        const saleData = {
            customer_id: customerId,
            items: cart,
            discount_rate: discountRate
        };

        fetch('api/complete_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Satış başarıyla tamamlandı.');
                window.location.href = 'customers.php';
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
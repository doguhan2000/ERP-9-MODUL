<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>9ERP - Ana Sayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center text-white mb-5">ERP Modülleri</h1>
        
        <div class="row g-4">
            <!-- İnsan Kaynakları -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/hr/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                👥
                            </div>
                            <h5 class="card-title">İnsan Kaynakları Yönetimi</h5>
                            <p class="card-text text-muted">Personel yönetimi ve İK süreçleri</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Muhasebe -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/accounting/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                📊
                            </div>
                            <h5 class="card-title">Muhasebe Yönetimi</h5>
                            <p class="card-text text-muted">Finansal işlemler ve raporlama</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Finans -->
            <div class="col-md-4">
                <div class="module-card">
                    <div class="card h-100 rounded-4 shadow-lg" style="cursor: pointer;" onclick="window.location.href='modules/finance/login.php'">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                💰
                            </div>
                            <h5 class="card-title">Finans Yönetimi</h5>
                            <p class="card-text text-muted">Nakit akışı ve finansal planlama</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Satış -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/sales/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                🛍️
                            </div>
                            <h5 class="card-title">Satış Yönetimi</h5>
                            <p class="card-text text-muted">Satış süreçleri ve takibi</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Satın Alma -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/purchasing/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                🛒
                            </div>
                            <h5 class="card-title">Satın Alma Yönetimi</h5>
                            <p class="card-text text-muted">Tedarik zinciri yönetimi</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Stok -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/inventory/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                📦
                            </div>
                            <h5 class="card-title">Stok Yönetimi</h5>
                            <p class="card-text text-muted">Envanter takibi ve yönetimi</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Üretim -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/production/" class="card h-100 rounded-4 shadow-lg text-decoration-none text-black">
                        <div class="card-body text-center p-4">
                            <div class="module-icon mb-3">
                                🏭
                            </div>
                            <h5 class="card-title">Üretim Planlama ve Kontrol</h5>
                            <p class="card-text text-muted">Üretim süreçleri yönetimi</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- CRM -->
            <div class="col-md-4">
                <a href="modules/crm/index.php" class="text-decoration-none">
                    <div class="module-card">
                        <div class="card h-100 rounded-4 shadow-lg">
                            <div class="card-body text-center p-4">
                                <div class="module-icon mb-3">
                                    🤝
                                </div>
                                <h5 class="card-title">Müşteri İlişkileri Yönetimi</h5>
                                <p class="card-text text-muted">CRM ve müşteri takibi</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- İş Zekası ve Raporlama -->
            <div class="col-md-4">
                <div class="module-card">
                    <a href="modules/analytics/index.php" class="text-decoration-none">
                        <div class="card h-100 rounded-4 shadow-lg">
                            <div class="card-body text-center p-4">
                                <div class="module-icon mb-3">
                                    📊
                                </div>
                                <h5 class="card-title">İş Zekası ve Raporlama</h5>
                                <p class="card-text text-muted">Veri analizi ve raporlama</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

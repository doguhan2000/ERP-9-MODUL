<div class="d-flex flex-column p-3 text-white h-100">
    <a href="../../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">9ERP</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="sales_reports.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'sales_reports.php' ? 'active' : '' ?>">
                <i class="bi bi-graph-up me-2"></i>
                Satış Raporları
            </a>
        </li>
        <li>
            <a href="customer_reports.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'customer_reports.php' ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i>
                Müşteri Raporları
            </a>
        </li>
        <li>
            <a href="inventory_reports.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'inventory_reports.php' ? 'active' : '' ?>">
                <i class="bi bi-box me-2"></i>
                Stok Raporları
            </a>
        </li>
        <li>
            <a href="financial_reports.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'financial_reports.php' ? 'active' : '' ?>">
                <i class="bi bi-currency-dollar me-2"></i>
                Finansal Raporlar
            </a>
        </li>
    </ul>
</div> 
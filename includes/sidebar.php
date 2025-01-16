<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">İK Modülleri</h5>
    </div>
    <div class="list-group">
        <a href="/9ERP/modules/hr" class="list-group-item list-group-item-action <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Personel Yönetimi
        </a>
        <a href="/9ERP/modules/hr/time.php" class="list-group-item list-group-item-action <?= $current_page == 'time.php' ? 'active' : '' ?>">
            <i class="bi bi-clock"></i> Zaman Yönetimi
        </a>
        <a href="/9ERP/modules/hr/departments.php" class="list-group-item list-group-item-action <?= $current_page == 'departments.php' ? 'active' : '' ?>">
            <i class="bi bi-diagram-3"></i> Departmanlar
        </a>
        <a href="/9ERP/modules/hr/positions.php" class="list-group-item list-group-item-action <?= $current_page == 'positions.php' ? 'active' : '' ?>">
            <i class="bi bi-briefcase"></i> Pozisyonlar
        </a>
        <a href="/9ERP/modules/hr/reports.php" class="list-group-item list-group-item-action <?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Raporlar
        </a>
    </div>
</div>

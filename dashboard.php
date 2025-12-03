<?php
require_once 'config.php';
requireLogin();

$dashboardController = new DashboardController();
$stats = $dashboardController->getStats();
$recentSales = $dashboardController->getRecentSales(10);
$lowStockProducts = $dashboardController->getLowStockProducts(5);
$nearExpiryProducts = $dashboardController->getNearExpiryProducts(5);
$expiredProducts = $dashboardController->getExpiredProducts(5);

$role = strtolower($_SESSION['role']);
$page_title = $role === 'admin' ? 'لوحة التحكم | Admin Dashboard' : 'لوحة التحكم | Employee Dashboard';

// Start content
ob_start();
?>

<!-- Dashboard Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-success me-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo formatCurrency($stats['total_sales'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">إجمالي المبيعات | Total Sales</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-info me-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['total_products'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">المنتجات | Products</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-warning me-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">إجمالي المبيعات | Total Sales</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (User::isAdmin()): ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-danger me-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo $stats['active_users'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">المستخدمون النشطون | Active Users</p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-secondary me-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo formatCurrency($stats['total_expenses'] ?? 0); ?></h3>
                    <p class="text-muted mb-0">المصروفات | Expenses</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Recent Sales -->
    <div class="col-xl-8 mb-4">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">المبيعات الأخيرة | Recent Sales</h5>
                <span class="badge bg-light text-dark"><?php echo count($recentSales); ?> مبيعات</span>
            </div>
            <div class="card-body">
                <?php if (empty($recentSales)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد مبيعات حديثة | No recent sales found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الموظف | Employee</th>
                                    <th>المبلغ | Amount</th>
                                    <th>التاريخ | Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td><strong class="text-primary">#<?php echo $sale['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($sale['employee_name'] ?? $sale['username'] ?? 'Unknown'); ?></td>
                                    <td><?php echo formatCurrency($sale['total_amount']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('Y-m-d H:i', strtotime($sale['created_at'])); ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Alerts -->
    <div class="col-xl-4 mb-4">
        <?php if (!empty($lowStockProducts)): ?>
        <div class="content-card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>منخفض المخزون | Low Stock</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($lowStockProducts, 0, 5) as $product): ?>
                    <li class="mb-2">
                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                        <span class="badge bg-warning float-start"><?php echo $product['total_stock'] ?? 0; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($nearExpiryProducts)): ?>
        <div class="content-card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>قريب من الانتهاء | Near Expiry</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($nearExpiryProducts, 0, 5) as $batch): ?>
                    <li class="mb-2">
                        <strong><?php echo htmlspecialchars($batch['product_name']); ?></strong>
                        <br>
                        <small class="text-muted">
                            <?php echo date('Y-m-d', strtotime($batch['expiry_date'])); ?>
                            (<?php echo $batch['days_to_expiry']; ?> يوم متبقي)
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($expiredProducts)): ?>
        <div class="content-card">
            <div class="card-header bg-danger">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>منتهي الصلاحية | Expired</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach (array_slice($expiredProducts, 0, 5) as $batch): ?>
                    <li class="mb-2">
                        <strong><?php echo htmlspecialchars($batch['product_name']); ?></strong>
                        <br>
                        <small class="text-danger">
                            <?php echo date('Y-m-d', strtotime($batch['expiry_date'])); ?>
                            (منتهي منذ <?php echo abs($batch['days_expired']); ?> يوم)
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions Row -->
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <h5 class="mb-0">إجراءات سريعة | Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="pos.php" class="btn btn-primary w-100 btn-custom">
                            <i class="fas fa-cash-register me-2"></i>
                            نقطة البيع | POS
                        </a>
                    </div>
                    
                    <?php if (User::isAdmin()): ?>
                    <div class="col-md-3 mb-3">
                        <a href="inventory.php" class="btn btn-success w-100 btn-custom">
                            <i class="fas fa-plus me-2"></i>
                            إضافة منتج | Add Product
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-3 mb-3">
                        <a href="expenses.php" class="btn btn-warning w-100 btn-custom">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            إضافة مصروف | Add Expense
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="sales.php" class="btn btn-info w-100 btn-custom">
                            <i class="fas fa-chart-bar me-2"></i>
                            التقارير | Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

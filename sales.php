<?php
require_once 'config.php';
requireLogin();

$page_title = 'تقارير المبيعات | Sales Reports';

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-0">تقارير المبيعات | Sales Reports</h4>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3 class="mb-0" id="total-sales">0</h3>
            <p class="text-muted mb-0">إجمالي المبيعات | Total Sales</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3 class="mb-0" id="total-revenue">0.00 ر.ي</h3>
            <p class="text-muted mb-0">إجمالي الإيرادات | Total Revenue</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3 class="mb-0" id="average-sale">0.00 ر.ي</h3>
            <p class="text-muted mb-0">متوسط البيع | Average Sale</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3 class="mb-0" id="today-sales">0.00 ر.ي</h3>
            <p class="text-muted mb-0">مبيعات اليوم | Today's Sales</p>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">من تاريخ | From Date</label>
        <input type="date" id="filter-start-date" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">إلى تاريخ | To Date</label>
        <input type="date" id="filter-end-date" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary w-100" onclick="loadSales()">
            <i class="fas fa-search me-2"></i>بحث | Search
        </button>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0">قائمة المبيعات | Sales List</h5>
    </div>
    <div class="card-body">
        <div id="sales-list">
            <p class="text-center text-muted py-4">جاري التحميل... | Loading...</p>
        </div>
    </div>
</div>

<!-- Sale Details Modal -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل البيع | Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sale-details-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set default dates (last 30 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    $('#filter-start-date').val(startDate.toISOString().split('T')[0]);
    $('#filter-end-date').val(endDate.toISOString().split('T')[0]);
    
    loadSales();
    loadStats();
});

function loadStats() {
    const startDate = $('#filter-start-date').val();
    const endDate = $('#filter-end-date').val();
    
    let url = 'api/sales.php?action=stats';
    if (startDate) url += '&start_date=' + startDate;
    if (endDate) url += '&end_date=' + endDate;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const stats = response.data;
                $('#total-sales').text(stats.total_sales || 0);
                $('#total-revenue').text((parseFloat(stats.total_revenue || 0)).toFixed(2) + ' ر.ي');
                $('#average-sale').text((parseFloat(stats.average_sale || 0)).toFixed(2) + ' ر.ي');
            }
        }
    });
    
    // Load today's sales
    const today = new Date().toISOString().split('T')[0];
    $.ajax({
        url: 'api/sales.php?action=stats&start_date=' + today + '&end_date=' + today,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                $('#today-sales').text((parseFloat(response.data.total_revenue || 0)).toFixed(2) + ' ر.ي');
            }
        }
    });
}

function loadSales() {
    const startDate = $('#filter-start-date').val();
    const endDate = $('#filter-end-date').val();
    
    let url = 'api/sales.php?action=list';
    if (startDate) url += '&start_date=' + startDate;
    if (endDate) url += '&end_date=' + endDate;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displaySales(response.data);
                loadStats();
            }
        }
    });
}

function displaySales(sales) {
    if (sales.length === 0) {
        $('#sales-list').html('<p class="text-center text-muted py-4">لا توجد مبيعات | No sales found</p>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>#</th><th>التاريخ | Date</th><th>الموظف | Employee</th><th>المبلغ | Amount</th><th>الإجراءات | Actions</th>';
    html += '</tr></thead><tbody>';
    
    sales.forEach(sale => {
        const date = new Date(sale.created_at);
        html += `
            <tr>
                <td><strong>#${sale.id}</strong></td>
                <td>${date.toLocaleDateString('ar-SA')} ${date.toLocaleTimeString('ar-SA', {hour: '2-digit', minute: '2-digit'})}</td>
                <td>${sale.employee_name || sale.username || '-'}</td>
                <td>
                    <strong>${(parseFloat(sale.total_amount) - parseFloat(sale.return_amount || 0)).toFixed(2)} ر.ي</strong>
                    ${parseFloat(sale.return_amount || 0) > 0 ? '<br><small class="text-danger">(مرتجع: ' + parseFloat(sale.return_amount).toFixed(2) + ' ر.ي)</small>' : ''}
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewSaleDetails(${sale.id})">
                        <i class="fas fa-eye"></i> عرض | View
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#sales-list').html(html);
}

function viewSaleDetails(saleId) {
    $.ajax({
        url: 'api/sales.php?action=details&id=' + saleId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                displaySaleDetails(response.data);
                $('#saleDetailsModal').modal('show');
            }
        }
    });
}

function displaySaleDetails(sale) {
    let html = `
        <div class="mb-3">
            <strong>رقم البيع | Sale ID:</strong> #${sale.id}<br>
            <strong>التاريخ | Date:</strong> ${new Date(sale.created_at).toLocaleString('ar-SA')}<br>
            <strong>الموظف | Employee:</strong> ${sale.employee_name || sale.username || '-'}<br>
            <strong>الإجمالي | Total:</strong> ${(parseFloat(sale.total_amount) - parseFloat(sale.return_amount || 0)).toFixed(2)} ر.ي
            ${parseFloat(sale.return_amount || 0) > 0 ? '<br><strong class="text-danger">المرتجع | Returns:</strong> ' + parseFloat(sale.return_amount).toFixed(2) + ' ر.ي' : ''}
        </div>
        <hr>
        <h6>المنتجات | Products:</h6>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>المنتج | Product</th>
                    <th>الوحدة | Unit</th>
                    <th>الكمية | Quantity</th>
                    <th>السعر | Price</th>
                    <th>الإجمالي | Total</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    if (sale.items && sale.items.length > 0) {
        sale.items.forEach(item => {
            const itemTotal = parseFloat(item.price_at_moment) * parseInt(item.quantity);
            html += `
                <tr>
                    <td>${item.product_name || '-'}</td>
                    <td>${item.unit_sold || '-'}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.price_at_moment).toFixed(2)} ر.ي</td>
                    <td>${itemTotal.toFixed(2)} ر.ي</td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="5" class="text-center text-muted">لا توجد منتجات | No items</td></tr>';
    }
    
    html += '</tbody></table>';
    $('#sale-details-content').html(html);
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


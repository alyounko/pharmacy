<?php
require_once 'config.php';
requireLogin();

$page_title = 'إرجاع المنتجات | Product Returns';

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">إرجاع المنتجات | Product Returns</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReturnModal">
                <i class="fas fa-undo me-2"></i>إرجاع منتج | Return Product
            </button>
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
        <button class="btn btn-primary w-100" onclick="loadReturns()">
            <i class="fas fa-search me-2"></i>بحث | Search
        </button>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0">قائمة المرتجعات | Returns List</h5>
    </div>
    <div class="card-body">
        <div id="returns-list">
            <p class="text-center text-muted py-4">جاري التحميل... | Loading...</p>
        </div>
    </div>
</div>

<!-- Add Return Modal -->
<div class="modal fade" id="addReturnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إرجاع منتج | Return Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-return-form">
                    <div class="mb-3">
                        <label class="form-label">رقم البيع | Sale ID *</label>
                        <input type="number" class="form-control" name="sale_id" id="sale-id" required>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="loadSaleItems()">تحميل عناصر البيع | Load Sale Items</button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عنصر البيع | Sale Item *</label>
                        <select class="form-select" name="sale_item_id" id="sale-items" required>
                            <option value="">اختر عنصر البيع | Select Sale Item</option>
                        </select>
                    </div>
                    <div id="return-details" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">المنتج | Product</label>
                            <input type="text" class="form-control" id="return-product" readonly>
                            <input type="hidden" name="product_id" id="return-product-id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الدفعة | Batch</label>
                            <select class="form-select" name="batch_id" id="return-batch">
                                <option value="">اختر دفعة | Select Batch</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الوحدة | Unit</label>
                            <input type="text" class="form-control" name="unit_returned" id="return-unit" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الكمية المرجعة | Return Quantity *</label>
                            <input type="number" class="form-control" name="quantity" id="return-quantity" min="1" required>
                            <small class="text-muted">الكمية المتاحة للرجوع: <span id="available-quantity">0</span></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">السعر | Price *</label>
                            <input type="number" class="form-control" name="price_at_return" id="return-price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">سبب الإرجاع | Return Reason</label>
                            <textarea class="form-control" name="return_reason" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ الإرجاع | Return Date *</label>
                            <input type="date" class="form-control" name="return_date" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addReturn()" id="submit-return" disabled>إرجاع | Return</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set default dates
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    $('#filter-start-date').val(startDate.toISOString().split('T')[0]);
    $('#filter-end-date').val(endDate.toISOString().split('T')[0]);
    
    // Set default return date to today
    $('input[name="return_date"]').val(new Date().toISOString().split('T')[0]);
    
    loadReturns();
});

function loadSaleItems() {
    const saleId = $('#sale-id').val();
    if (!saleId) {
        alert('يرجى إدخال رقم البيع | Please enter sale ID');
        return;
    }
    
    $.ajax({
        url: 'api/sales.php?action=details&id=' + saleId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data && response.data.items) {
                let html = '<option value="">اختر عنصر البيع | Select Sale Item</option>';
                response.data.items.forEach(item => {
                    html += `<option value="${item.id}" 
                        data-product-id="${item.product_id}" 
                        data-product-name="${item.product_name}"
                        data-batch-id="${item.batch_id || ''}"
                        data-unit="${item.unit_sold}"
                        data-quantity="${item.quantity}"
                        data-price="${item.price_at_moment}">${item.product_name} - ${item.quantity} ${item.unit_sold} - ${item.price_at_moment} ر.ي</option>`;
                });
                $('#sale-items').html(html);
                $('#return-details').hide();
                $('#submit-return').prop('disabled', true);
            } else {
                alert('لم يتم العثور على البيع | Sale not found');
            }
        }
    });
}

$('#sale-items').on('change', function() {
    const selected = $(this).find('option:selected');
    if (selected.val()) {
        const productId = selected.data('product-id');
        const productName = selected.data('product-name');
        const batchId = selected.data('batch-id');
        const unit = selected.data('unit');
        const quantity = selected.data('quantity');
        const price = selected.data('price');
        
        $('#return-product-id').val(productId);
        $('#return-product').val(productName);
        $('#return-unit').val(unit);
        $('#return-quantity').attr('max', quantity);
        $('#available-quantity').text(quantity);
        $('#return-price').val(price);
        $('#return-details').show();
        $('#submit-return').prop('disabled', false);
        
        // Load batches for this product
        loadProductBatches(productId, batchId);
    } else {
        $('#return-details').hide();
        $('#submit-return').prop('disabled', true);
    }
});

function loadProductBatches(productId, selectedBatchId) {
    $.ajax({
        url: 'api/products.php?action=batches&product_id=' + productId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">اختر دفعة | Select Batch</option>';
                response.data.forEach(batch => {
                    const selected = (batch.id == selectedBatchId) ? 'selected' : '';
                    html += `<option value="${batch.id}" ${selected}>${batch.batch_number || 'N/A'} - ${batch.expiry_date} (${batch.stock_quantity})</option>`;
                });
                $('#return-batch').html(html);
            }
        }
    });
}

function addReturn() {
    const formData = {
        sale_id: parseInt($('#sale-id').val()),
        sale_item_id: parseInt($('#sale-items').val()),
        product_id: parseInt($('#return-product-id').val()),
        batch_id: $('#return-batch').val() || null,
        unit_returned: $('#return-unit').val(),
        quantity: parseInt($('#return-quantity').val()),
        price_at_return: parseFloat($('#return-price').val()),
        return_reason: $('textarea[name="return_reason"]').val() || null,
        return_date: $('input[name="return_date"]').val()
    };
    
    if (!formData.sale_id || !formData.sale_item_id || !formData.quantity || !formData.price_at_return || !formData.return_date) {
        alert('يرجى ملء جميع الحقول المطلوبة | Please fill all required fields');
        return;
    }
    
    if (formData.quantity > parseInt($('#available-quantity').text())) {
        alert('الكمية المرجعة أكبر من الكمية المباعة | Return quantity exceeds sold quantity');
        return;
    }
    
    $.ajax({
        url: 'api/returns.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إرجاع المنتج بنجاح | Product returned successfully\nالمبلغ المرتجع: ' + response.return_amount.toFixed(2) + ' ر.ي');
                $('#addReturnModal').modal('hide');
                $('#add-return-form')[0].reset();
                $('#return-details').hide();
                $('#submit-return').prop('disabled', true);
                loadReturns();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إرجاع المنتج'));
            }
        }
    });
}

function loadReturns() {
    const startDate = $('#filter-start-date').val();
    const endDate = $('#filter-end-date').val();
    
    let url = 'api/returns.php?action=list';
    if (startDate) url += '&start_date=' + startDate;
    if (endDate) url += '&end_date=' + endDate;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayReturns(response.data);
            }
        }
    });
}

function displayReturns(returns) {
    if (!returns || returns.length === 0) {
        $('#returns-list').html('<p class="text-center text-muted py-4">لا توجد مرتجعات | No returns found</p>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>#</th><th>التاريخ | Date</th><th>المنتج | Product</th><th>الكمية | Quantity</th><th>المبلغ | Amount</th><th>السبب | Reason</th><th>المستخدم | User</th>';
    html += '</tr></thead><tbody>';
    
    let total = 0;
    returns.forEach(ret => {
        const amount = parseFloat(ret.price_at_return) * parseInt(ret.quantity);
        total += amount;
        html += `
            <tr>
                <td><strong>#${ret.id}</strong></td>
                <td>${ret.return_date}</td>
                <td>${ret.product_name || '-'}</td>
                <td>${ret.quantity} ${ret.unit_returned}</td>
                <td><strong>${amount.toFixed(2)} ر.ي</strong></td>
                <td>${ret.return_reason || '-'}</td>
                <td>${ret.returned_by_name || ret.username || '-'}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    html += `<div class="mt-3"><strong>إجمالي المرتجعات | Total Returns: ${total.toFixed(2)} ر.ي</strong></div>`;
    $('#returns-list').html(html);
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


<?php
require_once 'config.php';
requireLogin();

$productId = $_GET['id'] ?? 0;
if (!$productId) {
    header('Location: inventory.php');
    exit;
}

$productController = new ProductController();
$product = $productController->getProductDetails($productId);

if (!$product) {
    header('Location: inventory.php');
    exit;
}

$page_title = 'تفاصيل المنتج | Product Details: ' . $product['name'];

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <a href="inventory.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-right me-2"></i>العودة | Back
        </a>
        <h4 class="mb-0">تفاصيل المنتج | Product Details: <?php echo htmlspecialchars($product['name']); ?></h4>
    </div>
</div>

<div class="row">
    <!-- Product Information -->
    <div class="col-md-6 mb-4">
        <div class="content-card">
            <div class="card-header">
                <h5 class="mb-0">معلومات المنتج | Product Information</h5>
            </div>
            <div class="card-body">
                <form id="product-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">اسم المنتج | Product Name *</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الفئة | Category</label>
                        <select class="form-select" name="category_id" id="product-category">
                            <option value="">اختر الفئة | Select Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف | Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">إجمالي المخزون | Total Stock</label>
                        <input type="text" class="form-control" value="<?php echo $product['total_stock'] ?? 0; ?>" readonly>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Batches -->
    <div class="col-md-6 mb-4">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">الدفعات | Batches</h5>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                    <i class="fas fa-plus"></i> إضافة | Add
                </button>
            </div>
            <div class="card-body">
                <div id="batches-list">
                    <?php if (!empty($product['batches'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>رقم الدفعة | Batch #</th>
                                        <th>تاريخ الانتهاء | Expiry Date</th>
                                        <th>الكمية | Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product['batches'] as $batch): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($batch['batch_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($batch['expiry_date']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $batch['stock_quantity']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">لا توجد دفعات | No batches</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Units -->
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">الوحدات | Units</h5>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                    <i class="fas fa-plus"></i> إضافة | Add
                </button>
            </div>
            <div class="card-body">
                <div id="units-list">
                    <?php if (!empty($product['units'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>اسم الوحدة | Unit Name</th>
                                        <th>السعر | Price</th>
                                        <th>الكمية في الوحدة | Quantity in Unit</th>
                                        <th>افتراضي | Default</th>
                                        <th>الإجراءات | Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product['units'] as $unit): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                        <td><?php echo number_format($unit['price'], 2); ?> ر.ي</td>
                                        <td><?php echo $unit['quantity_in_unit']; ?></td>
                                        <td><?php echo $unit['is_default'] ? '<span class="badge bg-success">نعم</span>' : ''; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editUnit(<?php echo $unit['id']; ?>, <?php echo htmlspecialchars(json_encode($unit)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">لا توجد وحدات | No units</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Batch Modal -->
<div class="modal fade" id="addBatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة دفعة جديدة | Add New Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-batch-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">رقم الدفعة | Batch Number (optional)</label>
                        <input type="text" class="form-control" name="batch_number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ الانتهاء | Expiry Date *</label>
                        <input type="date" class="form-control" name="expiry_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية | Stock Quantity *</label>
                        <input type="number" class="form-control" name="stock_quantity" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addBatch()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة وحدة جديدة | Add New Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-unit-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">اسم الوحدة | Unit Name *</label>
                        <input type="text" class="form-control" name="unit_name" placeholder="Box, Strip, Pill, etc." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السعر | Price *</label>
                        <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية في الوحدة | Quantity in Unit *</label>
                        <input type="number" class="form-control" name="quantity_in_unit" min="1" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                            <label class="form-check-label" for="is_default">
                                جعلها الوحدة الافتراضية | Set as default unit
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addUnit()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الوحدة | Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-unit-form">
                    <input type="hidden" name="unit_id" id="edit-unit-id">
                    <div class="mb-3">
                        <label class="form-label">اسم الوحدة | Unit Name *</label>
                        <input type="text" class="form-control" name="unit_name" id="edit-unit-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السعر | Price *</label>
                        <input type="number" class="form-control" name="price" id="edit-unit-price" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية في الوحدة | Quantity in Unit *</label>
                        <input type="number" class="form-control" name="quantity_in_unit" id="edit-unit-quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" id="edit-is-default">
                            <label class="form-check-label" for="edit-is-default">
                                جعلها الوحدة الافتراضية | Set as default unit
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUnit()">حفظ | Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadCategories();
    
    // Set current category
    const currentCategoryId = <?php echo $product['category_id'] ?? 'null'; ?>;
    setTimeout(function() {
        if (currentCategoryId) {
            $('#product-category').val(currentCategoryId);
        }
    }, 500);
});

function loadCategories() {
    $.ajax({
        url: 'api/categories.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">اختر الفئة | Select Category</option>';
                response.data.forEach(category => {
                    html += `<option value="${category.id}">${category.name}</option>`;
                });
                $('#product-category').html(html);
                
                // Set current category
                const currentCategoryId = <?php echo $product['category_id'] ?? 'null'; ?>;
                if (currentCategoryId) {
                    $('#product-category').val(currentCategoryId);
                }
            }
        }
    });
}

function addBatch() {
    const formData = {
        product_id: $('input[name="product_id"]', '#add-batch-form').val(),
        batch_number: $('input[name="batch_number"]', '#add-batch-form').val() || null,
        expiry_date: $('input[name="expiry_date"]', '#add-batch-form').val(),
        stock_quantity: parseInt($('input[name="stock_quantity"]', '#add-batch-form').val())
    };
    
    if (!formData.expiry_date || !formData.stock_quantity) {
        alert('يرجى إدخال جميع البيانات المطلوبة | Please enter all required fields');
        return;
    }
    
    $.ajax({
        url: 'api/products.php?action=add_batch',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة الدفعة بنجاح | Batch added successfully');
                $('#addBatchModal').modal('hide');
                $('#add-batch-form')[0].reset();
                location.reload();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة الدفعة'));
            }
        }
    });
}

function addUnit() {
    const formData = {
        product_id: $('input[name="product_id"]', '#add-unit-form').val(),
        unit_name: $('input[name="unit_name"]', '#add-unit-form').val(),
        price: parseFloat($('input[name="price"]', '#add-unit-form').val()),
        quantity_in_unit: parseInt($('input[name="quantity_in_unit"]', '#add-unit-form').val()),
        is_default: $('#is_default').is(':checked')
    };
    
    if (!formData.unit_name || !formData.price || !formData.quantity_in_unit) {
        alert('يرجى إدخال جميع البيانات المطلوبة | Please enter all required fields');
        return;
    }
    
    $.ajax({
        url: 'api/products.php?action=add_unit',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة الوحدة بنجاح | Unit added successfully');
                $('#addUnitModal').modal('hide');
                $('#add-unit-form')[0].reset();
                location.reload();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة الوحدة'));
            }
        }
    });
}

function editUnit(unitId, unit) {
    $('#edit-unit-id').val(unitId);
    $('#edit-unit-name').val(unit.unit_name);
    $('#edit-unit-price').val(unit.price);
    $('#edit-unit-quantity').val(unit.quantity_in_unit);
    $('#edit-is-default').prop('checked', unit.is_default == 1);
    $('#editUnitModal').modal('show');
}

function updateUnit() {
    const formData = {
        unit_id: $('#edit-unit-id').val(),
        unit_name: $('#edit-unit-name').val(),
        price: parseFloat($('#edit-unit-price').val()),
        quantity_in_unit: parseInt($('#edit-unit-quantity').val()),
        is_default: $('#edit-is-default').is(':checked')
    };
    
    $.ajax({
        url: 'api/products.php?action=update_unit',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم تحديث الوحدة بنجاح | Unit updated successfully');
                $('#editUnitModal').modal('hide');
                location.reload();
            } else {
                alert('خطأ: ' + (response.message || 'فشل تحديث الوحدة'));
            }
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


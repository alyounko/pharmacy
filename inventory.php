<?php
require_once 'config.php';
requireLogin(); // Changed from requireAdmin() to allow employees

$page_title = 'إدارة المخزون | Inventory Management';

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">إدارة المخزون | Inventory Management</h4>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-tag me-2"></i>إضافة فئة | Add Category
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>إضافة منتج | Add Product
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <input type="text" id="search-products" class="form-control" 
               placeholder="ابحث عن منتج | Search products...">
    </div>
    <div class="col-md-6">
        <select id="filter-category" class="form-select">
            <option value="">جميع الفئات | All Categories</option>
        </select>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0">المنتجات | Products</h5>
    </div>
    <div class="card-body">
        <div id="products-list">
            <p class="text-center text-muted py-4">جاري التحميل... | Loading...</p>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة فئة جديدة | Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-category-form">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة | Category Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف | Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addCategory()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة منتج جديد | Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-product-form">
                    <div class="mb-3">
                        <label class="form-label">اسم المنتج | Product Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الفئة | Category</label>
                        <select class="form-select" name="category_id" id="product-category">
                            <option value="">اختر الفئة | Select Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف | Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addProduct()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadCategories().then(loadProducts);
    
    $('#search-products').on('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            loadProducts();
        }, 300);
    });
    
    $('#filter-category').on('change', function() {
        loadProducts();
    });
});

function loadProducts() {
    const search = $('#search-products').val();
    const category = $('#filter-category').val();
    
    let url = 'api/products.php?action=list';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (category) url += '&category_id=' + category;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayProducts(response.data);
            } else {
                $('#products-list').html('<p class="text-center text-danger py-4">خطأ في تحميل المنتجات | Error loading products</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading products:', error);
            $('#products-list').html('<p class="text-center text-danger py-4">خطأ في الاتصال | Connection error</p>');
        }
    });
}

function displayProducts(products) {
    if (!products || products.length === 0) {
        $('#products-list').html('<p class="text-center text-muted py-4">لا توجد منتجات | No products found</p>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>الاسم | Name</th><th>الفئة | Category</th><th>المخزون | Stock</th><th>الإجراءات | Actions</th>';
    html += '</tr></thead><tbody>';
    
    products.forEach(product => {
        html += `
            <tr>
                <td><strong>${product.name || 'N/A'}</strong></td>
                <td>${product.category_name || '-'}</td>
                <td><span class="badge bg-info">${product.total_stock || 0}</span></td>
                <td>
                    <a href="product_details.php?id=${product.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> عرض | View
                    </a>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#products-list').html(html);
}

function loadCategories() {
    return $.ajax({
        url: 'api/categories.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">اختر الفئة | Select Category</option>';
                let filterHtml = '<option value="">جميع الفئات | All Categories</option>';
                response.data.forEach(category => {
                    html += `<option value="${category.id}">${category.name}</option>`;
                    filterHtml += `<option value="${category.id}">${category.name}</option>`;
                });
                $('#product-category').html(html);
                $('#filter-category').html(filterHtml);
            }
        }
    });
}

function addCategory() {
    const formData = {
        name: $('input[name="name"]', '#add-category-form').val(),
        description: $('textarea[name="description"]', '#add-category-form').val() || null
    };
    
    if (!formData.name) {
        alert('يرجى إدخال اسم الفئة | Please enter category name');
        return;
    }
    
    $.ajax({
        url: 'api/categories.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة الفئة بنجاح | Category added successfully');
                $('#addCategoryModal').modal('hide');
                $('#add-category-form')[0].reset();
                loadCategories().then(loadProducts);
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة الفئة'));
            }
        }
    });
}

function addProduct() {
    const formData = {
        name: $('input[name="name"]', '#add-product-form').val(),
        category_id: $('#product-category').val() || null,
        description: $('textarea[name="description"]', '#add-product-form').val() || null
    };
    
    if (!formData.name) {
        alert('يرجى إدخال اسم المنتج | Please enter product name');
        return;
    }
    
    $.ajax({
        url: 'api/products.php?action=add',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة المنتج بنجاح | Product added successfully');
                $('#addProductModal').modal('hide');
                $('#add-product-form')[0].reset();
                loadProducts();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة المنتج'));
            }
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

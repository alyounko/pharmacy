<?php
require_once 'config.php';
requireLogin();

$page_title = 'نقطة البيع | Point of Sale';

ob_start();
?>

<div class="row">
    <!-- Product Search & Selection -->
    <div class="col-lg-5 mb-4">
        <div class="content-card">
            <div class="card-header">
                <h5 class="mb-0">البحث عن المنتجات | Search Products</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="product-search" class="form-control" 
                           placeholder="ابحث بالاسم أو الباركود | Search by name or barcode">
                </div>
                <div id="product-results" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-center text-muted">ابدأ البحث عن منتج | Start searching for a product</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Section -->
    <div class="col-lg-7 mb-4">
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">سلة التسوق | Shopping Cart</h5>
                <button class="btn btn-sm btn-light" id="clear-cart">
                    <i class="fas fa-trash"></i> مسح | Clear
                </button>
            </div>
            <div class="card-body">
                <div id="cart-items" style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                    <p class="text-center text-muted py-4">السلة فارغة | Cart is empty</p>
                </div>
                
                <div class="border-top pt-3">
                    <div class="row mb-2">
                        <div class="col-6"><strong>المجموع الفرعي | Subtotal:</strong></div>
                        <div class="col-6 text-end"><strong id="subtotal">0.00 ر.ي</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12"><strong>الإجمالي | Total:</strong></div>
                        <div class="col-12 text-end">
                            <h3 class="text-primary mb-0" id="total">0.00 ر.ي</h3>
                        </div>
                    </div>
                    
                    <button class="btn btn-success btn-lg w-100" id="complete-sale" disabled>
                        <i class="fas fa-check-circle me-2"></i>
                        إتمام البيع | Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختر الوحدة والدفعة | Select Unit & Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="product-details"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .product-item {
        cursor: pointer;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        transition: all 0.3s;
    }
    
    .product-item:hover {
        background: #f8f9fa;
        border-color: #667eea;
        transform: translateX(-5px);
    }
    
    .cart-item {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quantity-control button {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        background: white;
    }
    
    .quantity-control input {
        width: 60px;
        text-align: center;
    }
</style>

<script>
let cart = [];
let selectedProduct = null;

$(document).ready(function() {
    // Product search
    let searchTimeout;
    $('#product-search').on('input', function() {
        clearTimeout(searchTimeout);
        const search = $(this).val();
        
        if (search.length < 2) {
            $('#product-results').html('<p class="text-center text-muted">ابدأ البحث عن منتج | Start searching for a product</p>');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchProducts(search);
        }, 300);
    });
    
    // Clear cart
    $('#clear-cart').on('click', function() {
        cart = [];
        updateCart();
    });
    
    // Complete sale
    $('#complete-sale').on('click', function() {
        if (cart.length === 0) return;
        
        if (confirm('هل أنت متأكد من إتمام البيع؟ | Are you sure you want to complete this sale?')) {
            completeSale();
        }
    });
});

function searchProducts(search) {
    $.ajax({
        url: 'api/products.php?action=list&search=' + encodeURIComponent(search),
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '';
                response.data.forEach(product => {
                    html += `
                        <div class="product-item" onclick="selectProduct(${product.id})">
                            <strong>${product.name}</strong>
                            ${product.category_name ? '<br><small class="text-muted">' + product.category_name + '</small>' : ''}
                        </div>
                    `;
                });
                $('#product-results').html(html);
            } else {
                $('#product-results').html('<p class="text-center text-muted">لا توجد نتائج | No results found</p>');
            }
        },
        error: function() {
            $('#product-results').html('<p class="text-center text-danger">خطأ في البحث | Search error</p>');
        }
    });
}

function selectProduct(productId) {
    $.ajax({
        url: 'api/products.php?action=details&id=' + productId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                selectedProduct = response.data;
                showProductModal();
            }
        }
    });
}

function showProductModal() {
    if (!selectedProduct) return;
    
    let html = `
        <h6>${selectedProduct.name}</h6>
        <hr>
        <div class="mb-3">
            <label class="form-label">الوحدة | Unit:</label>
            <select class="form-select" id="selected-unit">
    `;
    
    if (selectedProduct.units && selectedProduct.units.length > 0) {
        selectedProduct.units.forEach(unit => {
            html += `<option value="${unit.id}" data-price="${unit.price}" data-quantity="${unit.quantity_in_unit}">${unit.unit_name} - ${unit.price} ر.ي</option>`;
        });
    } else {
        html += `<option>لا توجد وحدات | No units available</option>`;
    }
    
    html += `
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">الدفعة | Batch:</label>
            <select class="form-select" id="selected-batch">
                <option value="">اختر دفعة | Select batch</option>
    `;
    
    if (selectedProduct.batches && selectedProduct.batches.length > 0) {
        selectedProduct.batches.forEach(batch => {
            const daysToExpiry = Math.ceil((new Date(batch.expiry_date) - new Date()) / (1000 * 60 * 60 * 24));
            html += `<option value="${batch.id}" data-stock="${batch.stock_quantity}">${batch.batch_number || 'N/A'} - ${batch.expiry_date} (${batch.stock_quantity} متبقي | remaining)</option>`;
        });
    } else {
        html += `<option>لا توجد دفعات | No batches available</option>`;
    }
    
    html += `
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">الكمية | Quantity:</label>
            <input type="number" class="form-control" id="selected-quantity" value="1" min="1">
        </div>
        <button class="btn btn-primary w-100" onclick="addToCart()">
            <i class="fas fa-cart-plus me-2"></i>إضافة للسلة | Add to Cart
        </button>
    `;
    
    $('#product-details').html(html);
    $('#productModal').modal('show');
}

function addToCart() {
    if (!selectedProduct) return;
    
    const unitId = $('#selected-unit').val();
    const batchId = $('#selected-batch').val();
    const quantity = parseInt($('#selected-quantity').val()) || 1;
    
    if (!unitId) {
        alert('يرجى اختيار وحدة | Please select a unit');
        return;
    }
    
    if (!batchId) {
        alert('يرجى اختيار دفعة | Please select a batch');
        return;
    }
    
    const unit = selectedProduct.units.find(u => u.id == unitId);
    const batch = selectedProduct.batches.find(b => b.id == batchId);
    
    if (!unit || !batch) return;
    
    // Check stock
    const unitInfo = $('#selected-unit option:selected');
    const quantityInUnit = parseInt(unitInfo.data('quantity')) || 1;
    const requiredStock = quantity * quantityInUnit;
    
    if (batch.stock_quantity < requiredStock) {
        alert(`المخزون غير كافي | Insufficient stock. Available: ${batch.stock_quantity}`);
        return;
    }
    
    const cartItem = {
        product_id: selectedProduct.id,
        product_name: selectedProduct.name,
        batch_id: batchId,
        batch_number: batch.batch_number || 'N/A',
        unit_id: unitId,
        unit_name: unit.unit_name,
        quantity: quantity,
        price_at_moment: unit.price,
        quantity_in_unit: quantityInUnit
    };
    
    cart.push(cartItem);
    $('#productModal').modal('hide');
    updateCart();
    $('#product-search').val('');
    $('#product-results').html('<p class="text-center text-muted">ابدأ البحث عن منتج | Start searching for a product</p>');
}

function updateCart() {
    if (cart.length === 0) {
        $('#cart-items').html('<p class="text-center text-muted py-4">السلة فارغة | Cart is empty</p>');
        $('#complete-sale').prop('disabled', true);
        $('#subtotal').text('0.00 ر.ي');
        $('#total').text('0.00 ر.ي');
        return;
    }
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price_at_moment * item.quantity;
        subtotal += itemTotal;
        
        html += `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${item.product_name}</strong><br>
                        <small class="text-muted">${item.unit_name} - ${item.batch_number}</small>
                    </div>
                    <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="quantity-control">
                        <button onclick="updateQuantity(${index}, -1)">-</button>
                        <input type="number" value="${item.quantity}" min="1" 
                               onchange="updateQuantity(${index}, 0, $(this).val())">
                        <button onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <strong>${(itemTotal).toFixed(2)} ر.ي</strong>
                </div>
            </div>
        `;
    });
    
    $('#cart-items').html(html);
    $('#subtotal').text(subtotal.toFixed(2) + ' ر.ي');
    $('#total').text(subtotal.toFixed(2) + ' ر.ي');
    $('#complete-sale').prop('disabled', false);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateQuantity(index, change, newValue) {
    if (newValue !== undefined) {
        cart[index].quantity = parseInt(newValue) || 1;
    } else {
        cart[index].quantity = Math.max(1, cart[index].quantity + change);
    }
    updateCart();
}

function completeSale() {
    const items = cart.map(item => ({
        product_id: item.product_id,
        batch_id: item.batch_id,
        unit_sold: item.unit_name,
        quantity: item.quantity,
        price_at_moment: item.price_at_moment
    }));
    
    $.ajax({
        url: 'api/sales.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ items: items }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إتمام البيع بنجاح | Sale completed successfully!\nرقم البيع | Sale ID: #' + response.sale_id);
                cart = [];
                updateCart();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إتمام البيع | Failed to complete sale'));
            }
        },
        error: function() {
            alert('خطأ في الاتصال | Connection error');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

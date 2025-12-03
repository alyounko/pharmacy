<?php
require_once 'config.php';
requireLogin();

$page_title = 'المصروفات | Expenses';

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">المصروفات | Expenses</h4>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addExpenseTypeModal">
                    <i class="fas fa-tag me-2"></i>إضافة نوع مصروف | Add Expense Type
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus me-2"></i>إضافة مصروف | Add Expense
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <input type="date" id="filter-start-date" class="form-control">
    </div>
    <div class="col-md-4">
        <input type="date" id="filter-end-date" class="form-control">
    </div>
    <div class="col-md-4">
        <select id="filter-expense-type" class="form-select">
            <option value="">جميع الأنواع | All Types</option>
        </select>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0">قائمة المصروفات | Expenses List</h5>
    </div>
    <div class="card-body">
        <div id="expenses-list">
            <p class="text-center text-muted py-4">جاري التحميل... | Loading...</p>
        </div>
    </div>
</div>

<!-- Add Expense Type Modal -->
<div class="modal fade" id="addExpenseTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة نوع مصروف جديد | Add New Expense Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-expense-type-form">
                    <div class="mb-3">
                        <label class="form-label">اسم النوع | Type Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addExpenseType()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة مصروف جديد | Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-expense-form">
                    <div class="mb-3">
                        <label class="form-label">نوع المصروف | Expense Type</label>
                        <select class="form-select" name="expense_type_id" id="expense-type">
                            <option value="">اختر النوع | Select Type</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ | Amount *</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التاريخ | Date *</label>
                        <input type="date" class="form-control" name="expense_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات | Notes</label>
                        <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addExpense()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadExpenses();
    loadExpenseTypes();
    
    $('#filter-start-date, #filter-end-date, #filter-expense-type').on('change', function() {
        loadExpenses();
    });
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    $('input[name="expense_date"]').val(today);
});

function loadExpenseTypes() {
    $.ajax({
        url: 'api/expenses.php?action=types',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">اختر النوع | Select Type</option>';
                response.data.forEach(type => {
                    html += `<option value="${type.id}">${type.name}</option>`;
                });
                $('#expense-type').html(html);
                $('#filter-expense-type').html('<option value="">جميع الأنواع | All Types</option>' + html);
            }
        }
    });
}

function loadExpenses() {
    const startDate = $('#filter-start-date').val();
    const endDate = $('#filter-end-date').val();
    const expenseTypeId = $('#filter-expense-type').val();
    
    let url = 'api/expenses.php?action=list';
    if (startDate) url += '&start_date=' + startDate;
    if (endDate) url += '&end_date=' + endDate;
    if (expenseTypeId) url += '&expense_type_id=' + expenseTypeId;
    
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayExpenses(response.data);
            }
        }
    });
}

function displayExpenses(expenses) {
    if (expenses.length === 0) {
        $('#expenses-list').html('<p class="text-center text-muted py-4">لا توجد مصروفات | No expenses found</p>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>التاريخ | Date</th><th>النوع | Type</th><th>المبلغ | Amount</th><th>الملاحظات | Notes</th><th>المضيف | Created By</th><th>الإجراءات | Actions</th>';
    html += '</tr></thead><tbody>';
    
    let total = 0;
    expenses.forEach(expense => {
        total += parseFloat(expense.amount);
        html += `
            <tr>
                <td>${expense.expense_date}</td>
                <td>${expense.expense_type_name || '-'}</td>
                <td><strong>${parseFloat(expense.amount).toFixed(2)} ر.ي</strong></td>
                <td>${expense.note || '-'}</td>
                <td>${expense.created_by_name || expense.username || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteExpense(${expense.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    html += `<div class="mt-3"><strong>الإجمالي | Total: ${total.toFixed(2)}ر.ي</strong></div>`;
    $('#expenses-list').html(html);
}

function addExpense() {
    const formData = {
        expense_type_id: $('#expense-type').val() || null,
        amount: parseFloat($('input[name="amount"]').val()),
        expense_date: $('input[name="expense_date"]').val(),
        note: $('textarea[name="note"]').val() || null
    };
    
    if (!formData.amount || !formData.expense_date) {
        alert('يرجى إدخال المبلغ والتاريخ | Please enter amount and date');
        return;
    }
    
    $.ajax({
        url: 'api/expenses.php?action=add',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة المصروف بنجاح | Expense added successfully');
                $('#addExpenseModal').modal('hide');
                $('#add-expense-form')[0].reset();
                const today = new Date().toISOString().split('T')[0];
                $('input[name="expense_date"]').val(today);
                loadExpenses();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة المصروف'));
            }
        }
    });
}

function addExpenseType() {
    const name = $('input[name="name"]', '#add-expense-type-form').val();
    
    if (!name) {
        alert('يرجى إدخال اسم النوع | Please enter type name');
        return;
    }
    
    $.ajax({
        url: 'api/expense_types.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ name: name }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة نوع المصروف بنجاح | Expense type added successfully');
                $('#addExpenseTypeModal').modal('hide');
                $('#add-expense-type-form')[0].reset();
                loadExpenseTypes();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة نوع المصروف'));
            }
        }
    });
}

function deleteExpense(expenseId) {
    if (!confirm('هل أنت متأكد من حذف هذا المصروف؟ | Are you sure you want to delete this expense?')) {
        return;
    }
    
    $.ajax({
        url: 'api/expenses.php?id=' + expenseId,
        method: 'DELETE',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم حذف المصروف بنجاح | Expense deleted successfully');
                loadExpenses();
            } else {
                alert('خطأ: ' + (response.message || 'فشل حذف المصروف'));
            }
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


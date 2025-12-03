<?php
require_once 'config.php';
requireAdmin();

$page_title = 'إدارة الموظفين | Employee Management';

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">إدارة الموظفين | Employee Management</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-plus me-2"></i>إضافة موظف | Add Employee
            </button>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0">قائمة الموظفين | Employees List</h5>
    </div>
    <div class="card-body">
        <div id="employees-list">
            <p class="text-center text-muted py-4">جاري التحميل... | Loading...</p>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة موظف جديد | Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-employee-form">
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل | Full Name *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الهاتف | Phone</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المسمى الوظيفي | Job Title</label>
                        <input type="text" class="form-control" name="job_title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الراتب | Salary</label>
                        <input type="number" class="form-control" name="salary" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ التوظيف | Hiring Date</label>
                        <input type="date" class="form-control" name="hiring_date">
                    </div>
                    <hr>
                    <h6>إنشاء حساب مستخدم | Create User Account</h6>
                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم | Username</label>
                        <input type="text" class="form-control" name="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور | Password</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الدور | Role</label>
                        <select class="form-select" name="role">
                            <option value="Employee">موظف | Employee</option>
                            <option value="Admin">مدير | Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء | Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addEmployee()">إضافة | Add</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadEmployees();
});

function loadEmployees() {
    $.ajax({
        url: 'api/employees.php?action=list',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayEmployees(response.data);
            }
        },
        error: function() {
            $('#employees-list').html('<p class="text-center text-danger py-4">خطأ في تحميل البيانات | Error loading data</p>');
        }
    });
}

function displayEmployees(employees) {
    if (employees.length === 0) {
        $('#employees-list').html('<p class="text-center text-muted py-4">لا يوجد موظفين | No employees found</p>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
    html += '<th>الاسم | Name</th><th>الهاتف | Phone</th><th>المسمى | Job Title</th><th>الحالة | Status</th><th>حساب المستخدم | User Account</th><th>الإجراءات | Actions</th>';
    html += '</tr></thead><tbody>';
    
    employees.forEach(employee => {
        const statusBadge = employee.is_active 
            ? '<span class="badge bg-success">نشط | Active</span>'
            : '<span class="badge bg-danger">غير نشط | Inactive</span>';
        
        const userAccount = employee.username 
            ? `<span class="badge bg-primary">${employee.username} (${employee.role})</span>`
            : '<span class="badge bg-secondary">لا يوجد حساب | No Account</span>';
        
        html += `
            <tr>
                <td><strong>${employee.full_name}</strong></td>
                <td>${employee.phone || '-'}</td>
                <td>${employee.job_title || '-'}</td>
                <td>${statusBadge}</td>
                <td>${userAccount}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="editEmployee(${employee.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="toggleEmployeeStatus(${employee.id}, ${employee.is_active ? 0 : 1})">
                        <i class="fas fa-${employee.is_active ? 'ban' : 'check'}"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    $('#employees-list').html(html);
}

function addEmployee() {
    const formData = {
        full_name: $('input[name="full_name"]').val(),
        phone: $('input[name="phone"]').val() || null,
        job_title: $('input[name="job_title"]').val() || null,
        salary: $('input[name="salary"]').val() ? parseFloat($('input[name="salary"]').val()) : null,
        hiring_date: $('input[name="hiring_date"]').val() || null,
        username: $('input[name="username"]').val() || null,
        password: $('input[name="password"]').val() || null,
        role: $('select[name="role"]').val() || 'Employee'
    };
    
    if (!formData.full_name) {
        alert('يرجى إدخال الاسم الكامل | Please enter full name');
        return;
    }
    
    $.ajax({
        url: 'api/employees.php?action=add',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم إضافة الموظف بنجاح | Employee added successfully');
                $('#addEmployeeModal').modal('hide');
                $('#add-employee-form')[0].reset();
                loadEmployees();
            } else {
                alert('خطأ: ' + (response.message || 'فشل إضافة الموظف'));
            }
        }
    });
}

function editEmployee(employeeId) {
    alert('ميزة التعديل قيد التطوير | Edit feature under development');
}

function toggleEmployeeStatus(employeeId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (!confirm(`هل أنت متأكد من ${newStatus ? 'تفعيل' : 'تعطيل'} هذا الموظف؟ | Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this employee?`)) {
        return;
    }
    
    $.ajax({
        url: 'api/employees.php?action=' + action + '&id=' + employeeId,
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('تم تحديث الحالة بنجاح | Status updated successfully');
                loadEmployees();
            } else {
                alert('خطأ: ' + (response.message || 'فشل تحديث الحالة'));
            }
        }
    });
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>


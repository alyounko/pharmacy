<?php
require_once 'config.php';
requireLogin();

$currentUser = User::getCurrentUser();
$role = strtolower($currentUser->getRole());
$isAdmin = User::isAdmin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-family: sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            direction: rtl;
            font-size: 15px;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            right: 0;
            z-index: 1030;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            text-align: center;
            background: rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        
        .sidebar-header i {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
            display: block;
        }
        
        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .sidebar-header small {
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
            overflow-y: auto;
            flex-grow: 1;
            /* Custom Scrollbar for Firefox */
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.4) rgba(0, 0, 0, 0.2);
        }

        /* Custom Scrollbar for WebKit browsers (Chrome, Safari, Edge) */
        .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.4);
            border-radius: 10px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.6);
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 0;
            transition: all 0.3s ease;
            margin: 0.25rem 0.75rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-left: 12px;
            font-size: 1.1rem;
        }
        
        .sidebar-menu .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(-5px);
        }
        
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .main-content {
            margin-right: 280px;
            min-height: 100vh;
            transition: margin-right 0.3s ease-in-out;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 4px solid #667eea;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .content-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.5rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .content-card .card-body {
            padding: 2rem;
        }
        
        .btn-custom {
            border-radius: 10px;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            background: #f8f9fa;
            font-weight: 700;
            color: #495057;
            padding: 1rem;
            text-align: right;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }

            body.sidebar-mobile-open .sidebar-backdrop {
                opacity: 1;
                visibility: visible;
            }
        }
        
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1020; /* Below sidebar */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }
        
        .user-dropdown .dropdown-toggle {
            border-radius: 12px;
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            background: white;
            font-weight: 600;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
    </style>
    <script src="helpers/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-pills"></i>
            <h4>نظام الصيدلية</h4>
            <small>Pharmacy System</small>
        </div>
        
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>لوحة التحكم | Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'active' : ''; ?>" href="pos.php">
                        <i class="fas fa-cash-register"></i>
                        <span>نقطة البيع | POS</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>" href="inventory.php">
                        <i class="fas fa-boxes"></i>
                        <span>المخزون | Inventory</span>
                    </a>
                </li>
                
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>" href="employees.php">
                        <i class="fas fa-users"></i>
                        <span>الموظفين | Employees</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'expenses.php' ? 'active' : ''; ?>" href="expenses.php">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>المصروفات | Expenses</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                        <i class="fas fa-chart-line"></i>
                        <span>المبيعات | Sales Reports</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'returns.php' ? 'active' : ''; ?>" href="returns.php">
                        <i class="fas fa-undo"></i>
                        <span>المرتجعات | Returns</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0 fw-bold"><?php echo isset($page_title) ? $page_title : 'لوحة التحكم | Dashboard'; ?></h5>
            </div>
            
            <div class="dropdown user-dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($currentUser->getEmployeeName()); ?>
                    <span class="badge bg-<?php echo $isAdmin ? 'danger' : 'primary'; ?> ms-2">
                        <?php echo $isAdmin ? 'مدير | Admin' : 'موظف | Employee'; ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج | Logout
                    </a></li>
                </ul>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            <?php if (isset($content)) echo $content; ?>
        </div>
    </div>

    <div class="sidebar-backdrop"></div>
    <script>
        $(document).ready(function() {
            const sidebar = $('#sidebar');
            const backdrop = $('.sidebar-backdrop');
            const toggleBtn = $('#sidebar-toggle');

            function openSidebar() {
                $('body').addClass('sidebar-mobile-open');
                sidebar.addClass('show');
            }

            function closeSidebar() {
                $('body').removeClass('sidebar-mobile-open');
                sidebar.removeClass('show');
            }

            toggleBtn.on('click', function(e) {
                e.stopPropagation();
                if (sidebar.hasClass('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            backdrop.on('click', function() {
                closeSidebar();
            });

            // Clean up on window resize
            $(window).on('resize', function() {
                if ($(window).width() > 992) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>

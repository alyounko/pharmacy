<?php
class DashboardController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getStats() {
        try {
            $stats = [];
            
            // Total Sales (from sales table, accounting for returns)
            $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount - COALESCE(return_amount, 0)), 0) as total_sales FROM sales");
            $stats['total_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;
            
            // Total Products
            $stmt = $this->db->query("SELECT COUNT(*) as total_products FROM products");
            $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'] ?? 0;
            
            // Total Sales Count
            $stmt = $this->db->query("SELECT COUNT(*) as total_sales_count FROM sales");
            $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales_count'] ?? 0;
            
            // Active Users (Admin only)
            if (User::isAdmin()) {
                $stmt = $this->db->query("
                    SELECT COUNT(*) as active_users 
                    FROM users u 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE e.is_active = TRUE OR u.employee_id IS NULL
                ");
                $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'] ?? 0;
            }
            
            // Total Expenses
            $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses");
            $stats['total_expenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;
            
            return $stats;
        } catch(PDOException $e) {
            error_log("DashboardController::getStats error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentSales($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, u.username, e.full_name as employee_name 
                FROM sales s 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN employees e ON u.employee_id = e.id 
                ORDER BY s.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("DashboardController::getRecentSales error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLowStockProducts($limit = 10) {
        try {
            // Get products with low stock (sum of all batches)
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name,
                       COALESCE(SUM(b.stock_quantity), 0) as total_stock
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN batches b ON p.id = b.product_id 
                GROUP BY p.id
                HAVING total_stock <= 10
                ORDER BY total_stock ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("DashboardController::getLowStockProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNearExpiryProducts($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, p.name as product_name, c.name as category_name,
                       DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry
                FROM batches b 
                JOIN products p ON b.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE b.expiry_date IS NOT NULL 
                AND b.expiry_date > CURDATE()
                AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND b.stock_quantity > 0
                ORDER BY b.expiry_date ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("DashboardController::getNearExpiryProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExpiredProducts($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, p.name as product_name, c.name as category_name,
                       DATEDIFF(b.expiry_date, CURDATE()) as days_expired
                FROM batches b 
                JOIN products p ON b.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE b.expiry_date IS NOT NULL 
                AND b.expiry_date < CURDATE()
                AND b.stock_quantity > 0
                ORDER BY b.expiry_date ASC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("DashboardController::getExpiredProducts error: " . $e->getMessage());
            return [];
        }
    }
}
?>

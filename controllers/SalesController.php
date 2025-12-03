<?php
class SalesController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Create a new sale
    public function createSale($items, $userId) {
        try {
            $this->db->beginTransaction();
            
            // Calculate total
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['price_at_moment'] * $item['quantity'];
            }
            
            // Create sale record
            $stmt = $this->db->prepare("
                INSERT INTO sales (user_id, total_amount) 
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $totalAmount]);
            $saleId = $this->db->lastInsertId();
            
            // Create sale items and update batch stock
            foreach ($items as $item) {
                // Insert sale item
                $stmt = $this->db->prepare("
                    INSERT INTO sale_items (sale_id, product_id, batch_id, unit_sold, quantity, price_at_moment) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $saleId,
                    $item['product_id'],
                    $item['batch_id'] ?? null,
                    $item['unit_sold'],
                    $item['quantity'],
                    $item['price_at_moment']
                ]);
                
                // Update batch stock if batch_id is provided
                if (!empty($item['batch_id'])) {
                    // Calculate quantity in base units (pills)
                    $unitInfo = $this->getUnitInfo($item['product_id'], $item['unit_sold']);
                    $baseQuantity = $item['quantity'] * ($unitInfo['quantity_in_unit'] ?? 1);
                    
                    $stmt = $this->db->prepare("
                        UPDATE batches 
                        SET stock_quantity = stock_quantity - ? 
                        WHERE id = ? AND stock_quantity >= ?
                    ");
                    $stmt->execute([$baseQuantity, $item['batch_id'], $baseQuantity]);
                }
            }
            
            $this->db->commit();
            return ['success' => true, 'sale_id' => $saleId, 'total_amount' => $totalAmount];
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("SalesController::createSale error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get unit info
    private function getUnitInfo($productId, $unitName) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM product_units 
                WHERE product_id = ? AND unit_name = ?
            ");
            $stmt->execute([$productId, $unitName]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['quantity_in_unit' => 1];
        } catch(PDOException $e) {
            return ['quantity_in_unit' => 1];
        }
    }
    
    // Get sales with filters
    public function getSales($startDate = null, $endDate = null, $userId = null) {
        try {
            $sql = "SELECT s.*, 
                           COALESCE(s.return_amount, 0) as return_amount,
                           u.username, e.full_name as employee_name 
                    FROM sales s 
                    LEFT JOIN users u ON s.user_id = u.id 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(s.created_at) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(s.created_at) <= ?";
                $params[] = $endDate;
            }
            
            if ($userId) {
                $sql .= " AND s.user_id = ?";
                $params[] = $userId;
            }
            
            $sql .= " ORDER BY s.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("SalesController::getSales error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get sale details
    public function getSaleDetails($saleId) {
        try {
            // Get sale header
            $stmt = $this->db->prepare("
                SELECT s.*, u.username, e.full_name as employee_name 
                FROM sales s 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN employees e ON u.employee_id = e.id 
                WHERE s.id = ?
            ");
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sale) {
                return null;
            }
            
            // Get sale items
            $stmt = $this->db->prepare("
                SELECT si.*, p.name as product_name, b.batch_number, b.expiry_date 
                FROM sale_items si 
                LEFT JOIN products p ON si.product_id = p.id 
                LEFT JOIN batches b ON si.batch_id = b.id 
                WHERE si.sale_id = ?
            ");
            $stmt->execute([$saleId]);
            $sale['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $sale;
        } catch(PDOException $e) {
            error_log("SalesController::getSaleDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get sales statistics (accounting for returns)
    public function getSalesStats($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_sales,
                        SUM(s.total_amount - COALESCE(s.return_amount, 0)) as total_revenue,
                        AVG(s.total_amount - COALESCE(s.return_amount, 0)) as average_sale
                    FROM sales s
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(s.created_at) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(s.created_at) <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("SalesController::getSalesStats error: " . $e->getMessage());
            return [];
        }
    }
}
?>


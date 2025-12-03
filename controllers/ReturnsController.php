<?php
class ReturnsController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Create a return
    public function createReturn($data, $userId) {
        try {
            $this->db->beginTransaction();
            
            // Get sale item details
            $stmt = $this->db->prepare("
                SELECT si.*, s.total_amount, s.return_amount
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                WHERE si.id = ?
            ");
            $stmt->execute([$data['sale_item_id']]);
            $saleItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$saleItem) {
                throw new Exception('Sale item not found');
            }
            
            // Calculate return amount
            $returnAmount = $data['quantity'] * $data['price_at_return'];
            
            // Create return record
            $stmt = $this->db->prepare("
                INSERT INTO returns (sale_id, sale_item_id, product_id, batch_id, unit_returned, quantity, price_at_return, return_reason, returned_by_user_id, return_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['sale_id'],
                $data['sale_item_id'],
                $data['product_id'],
                $data['batch_id'] ?? null,
                $data['unit_returned'],
                $data['quantity'],
                $data['price_at_return'],
                $data['return_reason'] ?? null,
                $userId,
                $data['return_date']
            ]);
            $returnId = $this->db->lastInsertId();
            
            // Update sale return amount
            $stmt = $this->db->prepare("
                UPDATE sales 
                SET return_amount = return_amount + ? 
                WHERE id = ?
            ");
            $stmt->execute([$returnAmount, $data['sale_id']]);
            
            // Restore stock to batch if batch_id provided
            if (!empty($data['batch_id'])) {
                // Get unit info to calculate base quantity
                $unitInfo = $this->getUnitInfo($data['product_id'], $data['unit_returned']);
                $baseQuantity = $data['quantity'] * ($unitInfo['quantity_in_unit'] ?? 1);
                
                $stmt = $this->db->prepare("
                    UPDATE batches 
                    SET stock_quantity = stock_quantity + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$baseQuantity, $data['batch_id']]);
            }
            
            $this->db->commit();
            return ['success' => true, 'return_id' => $returnId, 'return_amount' => $returnAmount];
        } catch(Exception $e) {
            $this->db->rollBack();
            error_log("ReturnsController::createReturn error: " . $e->getMessage());
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
    
    // Get all returns
    public function getReturns($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT r.*, p.name as product_name, 
                           s.id as sale_number,
                           u.username, e.full_name as returned_by_name
                    FROM returns r 
                    LEFT JOIN products p ON r.product_id = p.id 
                    LEFT JOIN sales s ON r.sale_id = s.id
                    LEFT JOIN users u ON r.returned_by_user_id = u.id 
                    LEFT JOIN employees e ON u.employee_id = e.id 
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(r.return_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(r.return_date) <= ?";
                $params[] = $endDate;
            }
            
            $sql .= " ORDER BY r.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("ReturnsController::getReturns error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get return statistics
    public function getReturnStats($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_returns,
                        SUM(price_at_return * quantity) as total_return_amount
                    FROM returns 
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(return_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(return_date) <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>


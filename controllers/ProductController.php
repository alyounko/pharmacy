<?php
class ProductController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Get all products with their categories
    public function getAllProducts($search = '', $categoryId = null) {
        try {
            // Use subquery to calculate total stock to avoid GROUP BY issues
            $sql = "SELECT p.*, 
                           c.name as category_name,
                           COALESCE((
                               SELECT SUM(b.stock_quantity) 
                               FROM batches b 
                               WHERE b.product_id = p.id
                           ), 0) as total_stock
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            if ($categoryId) {
                $sql .= " AND p.category_id = ?";
                $params[] = $categoryId;
            }
            
            $sql .= " ORDER BY p.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure total_stock is always set
            foreach ($results as &$result) {
                if (!isset($result['total_stock'])) {
                    $result['total_stock'] = 0;
                }
            }
            
            return $results;
        } catch(PDOException $e) {
            error_log("ProductController::getAllProducts error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get product with batches and units
    public function getProductDetails($productId) {
        try {
            // Get product
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return null;
            }
            
            // Get batches
            $stmt = $this->db->prepare("
                SELECT * FROM batches 
                WHERE product_id = ? 
                ORDER BY expiry_date ASC
            ");
            $stmt->execute([$productId]);
            $product['batches'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get units
            $stmt = $this->db->prepare("
                SELECT * FROM product_units 
                WHERE product_id = ? 
                ORDER BY is_default DESC, unit_name ASC
            ");
            $stmt->execute([$productId]);
            $product['units'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total stock from batches
            $totalStock = 0;
            foreach ($product['batches'] as $batch) {
                $totalStock += $batch['stock_quantity'];
            }
            $product['total_stock'] = $totalStock;
            
            return $product;
        } catch(PDOException $e) {
            error_log("ProductController::getProductDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    // Add new product
    public function addProduct($data) {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO products (category_id, name, description) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['category_id'] ?? null,
                $data['name'],
                $data['description'] ?? null
            ]);
            $productId = $this->db->lastInsertId();
            
            $this->db->commit();
            return ['success' => true, 'product_id' => $productId];
        } catch(PDOException $e) {
            $this->db->rollBack();
            error_log("ProductController::addProduct error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Add batch to product
    public function addBatch($productId, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO batches (product_id, batch_number, expiry_date, stock_quantity) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $productId,
                $data['batch_number'] ?? null,
                $data['expiry_date'],
                $data['stock_quantity'] ?? 0
            ]);
            return ['success' => true, 'batch_id' => $this->db->lastInsertId()];
        } catch(PDOException $e) {
            error_log("ProductController::addBatch error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Add unit to product
    public function addUnit($productId, $data) {
        try {
            // If this is set as default, unset other defaults
            if (isset($data['is_default']) && $data['is_default']) {
                $stmt = $this->db->prepare("UPDATE product_units SET is_default = FALSE WHERE product_id = ?");
                $stmt->execute([$productId]);
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO product_units (product_id, unit_name, price, quantity_in_unit, is_default) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $productId,
                $data['unit_name'],
                $data['price'],
                $data['quantity_in_unit'],
                $data['is_default'] ?? false
            ]);
            return ['success' => true, 'unit_id' => $this->db->lastInsertId()];
        } catch(PDOException $e) {
            error_log("ProductController::addUnit error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Update unit
    public function updateUnit($unitId, $data) {
        try {
            // If setting as default, unset other defaults for this product
            if (isset($data['is_default']) && $data['is_default']) {
                $stmt = $this->db->prepare("
                    SELECT product_id FROM product_units WHERE id = ?
                ");
                $stmt->execute([$unitId]);
                $unit = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($unit) {
                    $stmt = $this->db->prepare("UPDATE product_units SET is_default = FALSE WHERE product_id = ? AND id != ?");
                    $stmt->execute([$unit['product_id'], $unitId]);
                }
            }
            
            $stmt = $this->db->prepare("
                UPDATE product_units 
                SET unit_name = ?, price = ?, quantity_in_unit = ?, is_default = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['unit_name'],
                $data['price'],
                $data['quantity_in_unit'],
                $data['is_default'] ?? false,
                $unitId
            ]);
            return ['success' => true];
        } catch(PDOException $e) {
            error_log("ProductController::updateUnit error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get available batches for a product (for POS)
    public function getAvailableBatches($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM batches 
                WHERE product_id = ? 
                AND stock_quantity > 0 
                AND expiry_date >= CURDATE()
                ORDER BY expiry_date ASC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("ProductController::getAvailableBatches error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get product units
    public function getProductUnits($productId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM product_units 
                WHERE product_id = ? 
                ORDER BY is_default DESC, unit_name ASC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>


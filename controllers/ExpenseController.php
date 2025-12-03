<?php
class ExpenseController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Get all expenses
    public function getExpenses($startDate = null, $endDate = null, $expenseTypeId = null) {
        try {
            $sql = "SELECT e.*, et.name as expense_type_name, u.username, emp.full_name as created_by_name 
                    FROM expenses e 
                    LEFT JOIN expense_types et ON e.expense_type_id = et.id 
                    LEFT JOIN users u ON e.created_by_user_id = u.id 
                    LEFT JOIN employees emp ON u.employee_id = emp.id 
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(e.expense_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(e.expense_date) <= ?";
                $params[] = $endDate;
            }
            
            if ($expenseTypeId) {
                $sql .= " AND e.expense_type_id = ?";
                $params[] = $expenseTypeId;
            }
            
            $sql .= " ORDER BY e.expense_date DESC, e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("ExpenseController::getExpenses error: " . $e->getMessage());
            return [];
        }
    }
    
    // Add expense
    public function addExpense($data, $userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO expenses (expense_type_id, created_by_user_id, amount, note, expense_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['expense_type_id'] ?? null,
                $userId,
                $data['amount'],
                $data['note'] ?? null,
                $data['expense_date']
            ]);
            return ['success' => true, 'expense_id' => $this->db->lastInsertId()];
        } catch(PDOException $e) {
            error_log("ExpenseController::addExpense error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Update expense
    public function updateExpense($expenseId, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE expenses 
                SET expense_type_id = ?, amount = ?, note = ?, expense_date = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['expense_type_id'] ?? null,
                $data['amount'],
                $data['note'] ?? null,
                $data['expense_date'],
                $expenseId
            ]);
            return ['success' => true];
        } catch(PDOException $e) {
            error_log("ExpenseController::updateExpense error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Delete expense
    public function deleteExpense($expenseId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$expenseId]);
            return ['success' => true];
        } catch(PDOException $e) {
            error_log("ExpenseController::deleteExpense error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get expense types
    public function getExpenseTypes() {
        try {
            $stmt = $this->db->query("SELECT * FROM expense_types ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    // Get expense statistics
    public function getExpenseStats($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_expenses,
                        SUM(amount) as total_amount,
                        AVG(amount) as average_amount
                    FROM expenses 
                    WHERE 1=1";
            $params = [];
            
            if ($startDate) {
                $sql .= " AND DATE(expense_date) >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND DATE(expense_date) <= ?";
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


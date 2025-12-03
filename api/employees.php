<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            try {
                $stmt = $db->query("
                    SELECT e.*, u.username, u.role, u.id as user_id
                    FROM employees e
                    LEFT JOIN users u ON e.id = u.employee_id
                    ORDER BY e.full_name ASC
                ");
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $employees]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? 'add';
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'add') {
            try {
                $db->beginTransaction();
                
                // Insert employee
                $stmt = $db->prepare("
                    INSERT INTO employees (full_name, phone, job_title, salary, hiring_date, is_active) 
                    VALUES (?, ?, ?, ?, ?, TRUE)
                ");
                $stmt->execute([
                    $data['full_name'],
                    $data['phone'] ?? null,
                    $data['job_title'] ?? null,
                    $data['salary'] ?? null,
                    $data['hiring_date'] ?? null
                ]);
                $employeeId = $db->lastInsertId();
                
                // Create user account if username and password provided
                // TEMPORARY: Store plain text password for testing
                if (!empty($data['username']) && !empty($data['password'])) {
                    // $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT); // Disabled for testing
                    $stmt = $db->prepare("
                        INSERT INTO users (employee_id, username, password_hash, role) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $employeeId,
                        $data['username'],
                        $data['password'], // Plain text for testing
                        $data['role'] ?? 'Employee'
                    ]);
                }
                
                $db->commit();
                echo json_encode(['success' => true, 'employee_id' => $employeeId]);
            } catch(PDOException $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } elseif ($action === 'activate' || $action === 'deactivate') {
            $employeeId = $_GET['id'] ?? 0;
            $isActive = $action === 'activate' ? 1 : 0;
            
            try {
                $stmt = $db->prepare("UPDATE employees SET is_active = ? WHERE id = ?");
                $stmt->execute([$isActive, $employeeId]);
                echo json_encode(['success' => true]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

switch ($method) {
    case 'GET':
        try {
            $stmt = $db->query("SELECT * FROM expense_types ORDER BY name ASC");
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $types]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Allow both Admin and Employee to add expense types
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $db->prepare("INSERT INTO expense_types (name) VALUES (?)");
            $stmt->execute([$data['name']]);
            echo json_encode(['success' => true, 'expense_type_id' => $db->lastInsertId()]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


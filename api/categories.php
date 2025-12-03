<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

switch ($method) {
    case 'GET':
        try {
            $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $categories]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Allow both Admin and Employee to add categories
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            echo json_encode(['success' => true, 'category_id' => $db->lastInsertId()]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>

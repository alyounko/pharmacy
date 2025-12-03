<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$productController = new ProductController();

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            $search = $_GET['search'] ?? '';
            $categoryId = $_GET['category_id'] ?? null;
            $products = $productController->getAllProducts($search, $categoryId);
            echo json_encode(['success' => true, 'data' => $products]);
        } elseif ($action === 'details') {
            $productId = $_GET['id'] ?? 0;
            $product = $productController->getProductDetails($productId);
            if ($product) {
                echo json_encode(['success' => true, 'data' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
        } elseif ($action === 'batches') {
            $productId = $_GET['product_id'] ?? 0;
            $batches = $productController->getAvailableBatches($productId);
            echo json_encode(['success' => true, 'data' => $batches]);
        } elseif ($action === 'units') {
            $productId = $_GET['product_id'] ?? 0;
            $units = $productController->getProductUnits($productId);
            echo json_encode(['success' => true, 'data' => $units]);
        }
        break;
        
    case 'POST':
        $action = $_GET['action'] ?? 'add';
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'add') {
            // Allow both Admin and Employee to add products
            $result = $productController->addProduct($data);
            echo json_encode($result);
        } elseif ($action === 'add_batch') {
            // Allow both Admin and Employee to add batches
            $productId = $data['product_id'] ?? 0;
            unset($data['product_id']);
            $result = $productController->addBatch($productId, $data);
            echo json_encode($result);
        } elseif ($action === 'add_unit') {
            $productId = $data['product_id'] ?? 0;
            unset($data['product_id']);
            $result = $productController->addUnit($productId, $data);
            echo json_encode($result);
        } elseif ($action === 'update_unit') {
            $unitId = $data['unit_id'] ?? 0;
            unset($data['unit_id']);
            $result = $productController->updateUnit($unitId, $data);
            echo json_encode($result);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


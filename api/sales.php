<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$salesController = new SalesController();
$userId = $_SESSION['user_id'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $filterUserId = User::isAdmin() ? ($_GET['user_id'] ?? null) : $userId;
            $sales = $salesController->getSales($startDate, $endDate, $filterUserId);
            echo json_encode(['success' => true, 'data' => $sales]);
        } elseif ($action === 'details') {
            $saleId = $_GET['id'] ?? 0;
            $sale = $salesController->getSaleDetails($saleId);
            if ($sale) {
                echo json_encode(['success' => true, 'data' => $sale]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sale not found']);
            }
        } elseif ($action === 'stats') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $stats = $salesController->getSalesStats($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $stats]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $salesController->createSale($data['items'] ?? [], $userId);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


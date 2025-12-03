<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$returnsController = new ReturnsController();
$userId = $_SESSION['user_id'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $returns = $returnsController->getReturns($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $returns]);
        } elseif ($action === 'stats') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $stats = $returnsController->getReturnStats($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $stats]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $returnsController->createReturn($data, $userId);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


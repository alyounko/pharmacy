<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$expenseController = new ExpenseController();
$userId = $_SESSION['user_id'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $expenseTypeId = $_GET['expense_type_id'] ?? null;
            $expenses = $expenseController->getExpenses($startDate, $endDate, $expenseTypeId);
            echo json_encode(['success' => true, 'data' => $expenses]);
        } elseif ($action === 'types') {
            $types = $expenseController->getExpenseTypes();
            echo json_encode(['success' => true, 'data' => $types]);
        } elseif ($action === 'stats') {
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $stats = $expenseController->getExpenseStats($startDate, $endDate);
            echo json_encode(['success' => true, 'data' => $stats]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $_GET['action'] ?? 'add';
        
        if ($action === 'add') {
            $result = $expenseController->addExpense($data, $userId);
            echo json_encode($result);
        } elseif ($action === 'update') {
            $expenseId = $data['expense_id'] ?? 0;
            unset($data['expense_id']);
            $result = $expenseController->updateExpense($expenseId, $data);
            echo json_encode($result);
        }
        break;
        
    case 'DELETE':
        $expenseId = $_GET['id'] ?? 0;
        $result = $expenseController->deleteExpense($expenseId);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>


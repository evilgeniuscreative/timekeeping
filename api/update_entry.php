<?php
require_once 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'], $data['field'], $data['value'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$allowedFields = ['date', 'start_time', 'lunch_start', 'lunch_end', 'end_time', 'pay_rate', 'paid'];
if (!in_array($data['field'], $allowedFields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid field']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE entries SET {$data['field']} = ? WHERE id = ?");
    $stmt->execute([
        $data['value'],
        $data['id']
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
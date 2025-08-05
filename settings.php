<?php
require_once 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $key = $_GET['key'] ?? null;
            if ($key) {
                $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
                $stmt->execute([$key]);
                $result = $stmt->fetch();
                
                if ($result) {
                    echo json_encode(['value' => $result['setting_value']]);
                } else {
                    echo json_encode(['value' => null]);
                }
            } else {
                $stmt = $pdo->query('SELECT * FROM settings');
                echo json_encode($stmt->fetchAll());
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['key'], $data['value'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing key or value']);
                break;
            }
            
            $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
            $stmt->execute([$data['key'], $data['value'], $data['value']]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}
?>

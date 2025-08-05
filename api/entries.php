<?php
require_once 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->query('SELECT * FROM entries ORDER BY date DESC, id DESC');
            $results = $stmt->fetchAll();
            
            // Clean up any null values to prevent PHP warnings
            foreach ($results as &$row) {
                $row['pay_rate'] = $row['pay_rate'] ? floatval($row['pay_rate']) : 18.00;
                $row['paid'] = $row['paid'] ? floatval($row['paid']) : 0.00;
            }
            
            echo json_encode($results);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                break;
            }

            $stmt = $pdo->prepare('INSERT INTO entries (date, start_time, lunch_start, lunch_end, end_time, pay_rate, paid) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                (!isset($data['date']) || $data['date'] === '' || $data['date'] === null) ? null : $data['date'],
                $data['start_time'] ?? null,
                $data['lunch_start'] ?? null,
                $data['lunch_end'] ?? null,
                $data['end_time'] ?? null,
                $data['pay_rate'] ?? 18.00,
                $data['paid'] ?? 0.00
            ]);
            
            echo json_encode(['id' => $pdo->lastInsertId(), 'success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing ID']);
                break;
            }

            $stmt = $pdo->prepare('UPDATE entries SET date=?, start_time=?, lunch_start=?, lunch_end=?, end_time=?, pay_rate=?, paid=? WHERE id=?');
            $stmt->execute([
                $data['date'],
                $data['start_time'],
                $data['lunch_start'],
                $data['lunch_end'],
                $data['end_time'],
                $data['pay_rate'],
                $data['paid'] ?? 0.00,
                $data['id']
            ]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing ID']);
                break;
            }

            $stmt = $pdo->prepare('DELETE FROM entries WHERE id = ?');
            $stmt->execute([$data['id']]);
            
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
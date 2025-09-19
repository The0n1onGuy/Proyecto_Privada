<?php
require_once 'connProperties.php';
require_once 'connectionDB.php';

header('Content-Type: application/json');

try {
    $conn = new connectionDB();
    $pdo = $conn->connect();

    $sql = "SELECT customerName, phone, city FROM customers";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['data' => [], 'error' => $e->getMessage()]);
}
?>
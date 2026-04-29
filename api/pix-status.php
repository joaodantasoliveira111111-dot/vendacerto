<?php
header('Content-Type: application/json; charset=utf-8');

$orderId = $_GET['orderId'] ?? '';
$ordersFile = __DIR__ . '/data/orders.json';

if (!$orderId || !file_exists($ordersFile)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
    exit;
}

$orders = json_decode(file_get_contents($ordersFile), true) ?: [];
if (!isset($orders[$orderId])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
    exit;
}

$order = $orders[$orderId];

echo json_encode([
    'success' => true,
    'orderId' => $orderId,
    'status' => $order['status'] ?? 'PENDING',
    'accessReleased' => $order['accessReleased'] ?? false,
    'memberAreaUrl' => $order['memberAreaUrl'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

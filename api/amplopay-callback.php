<?php
/**
 * Webhook/callback da Amplopay.
 *
 * Ajuste os campos abaixo conforme o payload real que o gateway enviar.
 * A ideia é salvar o status e liberar acesso quando status = OK / PAID / APPROVED.
 */
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

$logFile = __DIR__ . '/data/callbacks.log';
file_put_contents($logFile, date('c') . ' ' . $raw . PHP_EOL, FILE_APPEND);

$ordersFile = __DIR__ . '/data/orders.json';
$orders = file_exists($ordersFile) ? (json_decode(file_get_contents($ordersFile), true) ?: []) : [];

$transactionId = $payload['transactionId'] ?? $payload['id'] ?? null;
$status = $payload['status'] ?? null;
$orderId = $payload['metadata']['orderId'] ?? $payload['orderId'] ?? null;

if (!$orderId && $transactionId) {
    foreach ($orders as $id => $order) {
        if (($order['transactionId'] ?? null) === $transactionId) {
            $orderId = $id;
            break;
        }
    }
}

if ($orderId && isset($orders[$orderId])) {
    $orders[$orderId]['status'] = $status ?: $orders[$orderId]['status'];
    $orders[$orderId]['updatedAt'] = date('c');
    $orders[$orderId]['callback'] = $payload;

    $paidStatuses = ['OK', 'PAID', 'APPROVED', 'CONFIRMED', 'COMPLETED'];
    if (in_array(strtoupper((string)$orders[$orderId]['status']), $paidStatuses, true)) {
        $orders[$orderId]['accessReleased'] = true;
        $orders[$orderId]['memberAreaUrl'] = $config['member_area_url'];
    }

    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

echo json_encode(['success' => true]);

<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clean_digits($value) {
    return preg_replace('/\D+/', '', $value ?? '');
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    json_response(['success' => false, 'message' => 'Body JSON inválido.'], 400);
}

$name = trim($input['name'] ?? '');
$email = trim(strtolower($input['email'] ?? ''));
$phone = trim($input['phone'] ?? '');
$document = trim($input['document'] ?? '');

if ($name === '' || $email === '' || $phone === '' || $document === '') {
    json_response(['success' => false, 'message' => 'Preencha nome, e-mail, WhatsApp e CPF.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'E-mail inválido.'], 400);
}

if (strlen(clean_digits($phone)) < 10) {
    json_response(['success' => false, 'message' => 'WhatsApp inválido.'], 400);
}

if (strlen(clean_digits($document)) < 11) {
    json_response(['success' => false, 'message' => 'CPF inválido.'], 400);
}

if (strpos($config['public_key'], 'COLOQUE_') !== false || strpos($config['secret_key'], 'COLOQUE_') !== false) {
    json_response(['success' => false, 'message' => 'Configure suas chaves da Amplopay no arquivo api/config.php ou em variáveis de ambiente.'], 500);
}

$identifier = 'rc_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
$orderId = $identifier;

$body = [
    'identifier' => $identifier,
    'amount' => (float) $config['amount'],
    'client' => [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'document' => $document,
    ],
    'products' => [
        [
            'id' => $config['product_id'],
            'name' => $config['product_name'],
            'quantity' => 1,
            'price' => (float) $config['amount'],
        ],
    ],
    'dueDate' => date('Y-m-d', strtotime('+1 day')),
    'metadata' => [
        'provider' => 'Checkout Embutido',
        'orderId' => $orderId,
        'product' => $config['product_id'],
    ],
    'callbackUrl' => rtrim($config['site_url'], '/') . '/api/amplopay-callback.php',
];

$ch = curl_init($config['pix_endpoint']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-public-key: ' . $config['public_key'],
        'x-secret-key: ' . $config['secret_key'],
    ],
    CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
    json_response(['success' => false, 'message' => 'Erro de comunicação com a Amplopay.', 'details' => $curlError], 502);
}

$data = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300) {
    json_response([
        'success' => false,
        'message' => $data['message'] ?? $data['errorDescription'] ?? 'Erro ao gerar cobrança Pix.',
        'amplopay' => $data,
    ], $httpCode);
}

// Pedido não é salvo em arquivo local.

json_response([
    'success' => true,
    'orderId' => $orderId,
    'identifier' => $identifier,
    'transactionId' => $data['transactionId'] ?? null,
    'status' => $data['status'] ?? null,
    'pix' => $data['pix'] ?? null,
    'order' => $data['order'] ?? null,
]);

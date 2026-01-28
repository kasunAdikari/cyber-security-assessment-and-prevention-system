<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$code  = $input['code'] ?? '';

if (!$code) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty code.']);
    exit;
}

$OLLAMA_URL = 'http://localhost:11434/api/generate';
$MODEL = 'llama3';

// Simple one-shot prompt
$prompt = "Review the following code for vulnerabilities and suggest fixes:\n\n{$code}";

$payload = json_encode([
    'model'  => $MODEL,
    'prompt' => $prompt,
    'stream' => false
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$ch = curl_init($OLLAMA_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 120
]);

$res = curl_exec($ch);
$err = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $http >= 400) {
    http_response_code(500);
    echo json_encode(['error' => $err ?: "Ollama HTTP $http"]);
    exit;
}

$data = json_decode($res, true);
echo json_encode([
    'markdown' => $data['response'] ?? 'No response from model.'
]);
?>
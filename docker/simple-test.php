<?php

echo "=== TESTE BÁSICO DE CONECTIVIDADE ===\n\n";

// Teste interno (dentro da rede Docker)
echo "1. Teste interno (fila-nginx):\n";
$internal = testUrl('http://fila-nginx/');
echo "\n";

// Teste de rota da API
echo "2. Teste de rota API:\n";
$api = testUrl('http://fila-nginx/api/v1/authenticate', [
    'X-API-Key: HvmCv348usmHqPByLxmVIymFvdeiDsYQ0HwOal6VrH82Seli7KirSzLDASY2Yv1L'
]);
echo "\n";

// Informações do PHP
echo "3. Informações do ambiente:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' . "\n";

function testUrl($url, $headers = []) {
    echo "URL: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseHeader = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    
    curl_close($ch);
    
    echo "Status: $httpCode\n";
    
    if ($error) {
        echo "Error: $error\n";
    } else {
        echo "Headers:\n" . trim(preg_replace('/\s+/', ' ', $responseHeader)) . "\n";
        echo "Body (first 150 chars): " . substr(trim($responseBody), 0, 150) . "...\n";
    }
    
    return [
        'code' => $httpCode,
        'headers' => $responseHeader,
        'body' => $responseBody,
        'error' => $error
    ];
}


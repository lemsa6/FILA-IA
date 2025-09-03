<?php
// Conectar ao banco de dados usando as mesmas configurações do Laravel
$host = getenv('DB_HOST') ?: 'fila-db';
$database = getenv('DB_DATABASE') ?: 'fila_api';
$username = getenv('DB_USERNAME') ?: 'fila';
$password = getenv('DB_PASSWORD') ?: 'fila';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se a tabela api_keys existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'api_keys'");
    if ($stmt->rowCount() == 0) {
        echo "A tabela 'api_keys' não existe!\n";
        exit;
    }
    
    // Listar todas as chaves API
    $stmt = $pdo->query("SELECT * FROM api_keys");
    $apiKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($apiKeys) == 0) {
        echo "Não há chaves API cadastradas no banco de dados.\n";
    } else {
        echo "Chaves API encontradas:\n";
        foreach ($apiKeys as $key) {
            echo "ID: {$key['id']}, Key: {$key['key']}, Status: {$key['status']}\n";
        }
    }
    
    // Verificar a estrutura da tabela requests
    echo "\nEstrutura da tabela 'requests':\n";
    $stmt = $pdo->query("DESCRIBE requests");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "{$column['Field']} - {$column['Type']} - {$column['Key']}\n";
    }
    
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage() . "\n";
}

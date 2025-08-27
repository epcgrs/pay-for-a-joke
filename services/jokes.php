<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

function getRandomJoke() {
    $pdo = getConnection();

    try {
        // Consulta para buscar uma piada aleatória
        $stmt = $pdo->query("SELECT content FROM jokes ORDER BY RAND() LIMIT 1");
        $joke = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($joke) {
            return $joke['content'];
        } else {
            return "Nenhuma piada encontrada no banco de dados.";
        }
    } catch (PDOException $e) {
        return "Erro ao buscar piada: " . $e->getMessage();
    }
}

function generateAIJoke() {
    $apiKey = $_ENV['OPENROUTER_API_KEY'];
    $endpoint = $_ENV['OPENROUTER_ENDPOINT'] ?? 'https://openrouter.ai/api/v1/chat/completions';
    $referer  = $_ENV['APP_PUBLIC_URL'] ?? 'http://localhost';
    $appTitle = $_ENV['APP_TITLE'] ?? 'Piada Automática';
    $model    = $_ENV['OPENROUTER_MODEL'] ?? 'openai/gpt-3.5-turbo';

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
    "HTTP-Referer: $referer",
    "X-Title: $appTitle"
    ]);

    $data = [
    'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Você é um comediante brasileiro. Suas piadas devem ser curtas, engraçadas e em bom português.'],
            ['role' => 'user', 'content' => 'Conte uma piada de humor ácido, estilo stand-up brasileiro.']
        ],
        'max_tokens' => 100,
        'temperature' => 0.5
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);

    if (curl_errno($ch)) {
        return "Erro cURL: " . curl_error($ch);
    }
    curl_close($ch);

    $response = json_decode($res, true);

    if (isset($response['choices'][0]['message']['content'])) {
        return $response['choices'][0]['message']['content'];
    } elseif (isset($response['error']['message'])) {
        return "Erro da API: " . $response['error']['message'];
    } else {
        return "Erro inesperado. Resposta bruta: " . $res;
    }
}
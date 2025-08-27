<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../services/jokes.php';
require __DIR__ . '/../services/payments.php';
require __DIR__ . '/../database/database.php';


use Slim\Factory\AppFactory;
use Slim\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

// env

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

// Página inicial
$app->get('/', function (Request $request, Response $response) {
    ob_start();
    include __DIR__ . '/view/home.php';
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

// Como Funciona
$app->get('/como-funciona', function (Request $request, Response $response) {
    ob_start();
    include __DIR__ . '/view/como-funciona.php';
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

// Criar invoice
$app->post('/api/criar-invoice', function (Request $request, Response $response) {
    $response = $response->withHeader('Content-Type', 'application/json');

    try {
        $apiKey = $_ENV['COINOS_TOKEN'];
        $amountRaw = $request->getParsedBody()['amount'] ?? 10;
        $amount = filter_var($amountRaw, FILTER_VALIDATE_INT);
        if ($amount === false || $amount < 10 || $amount > 50000) {
            $response->getBody()->write(json_encode(['error' => 'Valor inválido']));
            return $response->withStatus(400);
        }

        $ch = curl_init($_ENV['COINOS_API_URL'] . 'invoice');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['invoice' => [
            'amount' => $amount,
            'type'   => 'lightning',
        ]]));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);

        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            $response->getBody()->write(json_encode(['error' => 'Erro ao criar invoice: ' . curl_error($ch)]));
            return $response->withStatus(500);
        }
        curl_close($ch);

        $invoice = json_decode($res, true);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Erro ao criar invoice: ' . $e->getMessage()]));
        return $response->withStatus(500);
    }

    if (!is_array($invoice) || isset($invoice['error'])) {
        $msg = $invoice['error'] ?? 'Resposta inválida';
        $response->getBody()->write(json_encode(['error' => 'Erro ao criar invoice: ' . $msg]));
        return $response->withStatus(500);
    }

    createPayment(
        $invoice['text'] ?? '',
        $invoice['amount'] ?? 0,
        $invoice['received'] ?? 0
    );

    $response->getBody()->write(json_encode(['invoice' => $invoice]));
    return $response;
});

// Verificar pagamento
$app->get('/api/checar/{id}', function (Request $request, Response $response, $args) {
    $response = $response->withHeader('Content-Type', 'application/json');
    $invoiceId = $args['id'];
    $apiKey = $_ENV['COINOS_TOKEN'];

    if (!preg_match('/^[A-Za-z0-9]+$/', $invoiceId)) {
        $response->getBody()->write(json_encode([
            'error' => 'Identificador de invoice inválido'
        ]));
        return $response->withStatus(400);
    }

    $ch = curl_init($_ENV['COINOS_API_URL'] . 'invoice/' . urlencode($invoiceId));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
    ]);
    $res = curl_exec($ch);
    if (curl_errno($ch)) {
        $response->getBody()->write(json_encode([
            'error' => 'Erro ao consultar invoice: ' . curl_error($ch)
        ]));
        curl_close($ch);
        return $response->withStatus(500);
    }
    curl_close($ch);
    $data = json_decode($res, true);
    if (!is_array($data) || !isset($data['received'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Resposta inválida ao consultar invoice'
        ]));
        return $response->withStatus(500);
    }

    if ((int)$data['received'] <= 0) {
        $response->getBody()->write(json_encode([
            'message'  => 'Pagamento não recebido',
            'piada'    => null,
            'received' => 0,
        ]));
        return $response;
    }

    $piada   = getRandomJoke();
    $piadaAI = generateAIJoke();

    updatePaymentStatus($invoiceId, $data['received'], date('Y-m-d H:i:s'));

    $ebook = null;
    if ((int)$data['received'] > 1999) {
        $ebook = getEbookRecommendation();
    }

    $response->getBody()->write(json_encode([
        'piada'    => $piada,
        'piadaAI'  => $piadaAI,
        'received' => $data['received'],
        'ebook'    => $ebook,
    ]));
    return $response;
});

$app->get('/generate/ebook', function (Request $request, Response $response) {
    // Resolve base storage directory from ENV. Accept absolute or relative paths.
    $base = $_ENV['STORAGE_URL'] ?? dirname(__DIR__) . '/storage';
    $base = rtrim($base, '/');
    if (strpos($base, '/') !== 0) { // relative to project root
        $base = dirname(__DIR__) . '/' . $base;
    }

    $ebookFile = $_ENV['EBOOK_FILE'] ?? 'cursobtc_mmzero.pdf';
    $file = $base . '/' . $ebookFile;

    if (!file_exists($file)) {
        $response->getBody()->write('Arquivo não encontrado.');
        return $response->withStatus(404);
    }
    $stream = new Stream(fopen($file, 'rb'));
    return $response
        ->withHeader('Content-Type', 'application/pdf')
        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($ebookFile) . '"')
        ->withBody($stream);
});


$app->run();
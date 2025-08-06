<?php
declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Database\Connection;
use AnwarSaeed\InvoiceProcessor\Core\Router;
use AnwarSaeed\InvoiceProcessor\Controllers\InvoiceController;
use AnwarSaeed\InvoiceProcessor\Services\InvoiceService;
use AnwarSaeed\InvoiceProcessor\Repositories\{
    InvoiceRepository,
    CustomerRepository,
    ProductRepository
};

// Initialize dependencies
$connection = new Connection("sqlite:".__DIR__."/../database/invoices.db");

$repositories = [
    'invoice'   => new InvoiceRepository($connection),
    'customer'  => new CustomerRepository($connection),
    'product'   => new ProductRepository($connection)
];

$services = [
    'invoice' => new InvoiceService(
        $repositories['invoice'],
        $repositories['customer'],
        $repositories['product']
    )
];

$controllers = [
    'invoice' => new InvoiceController($services['invoice'])
];

// Configure router
$router = new Router();

//Api routes
// In public/index.php

$router->add('GET', '/api/invoices', function() use ($controllers) {
    $page = (int) ($_GET['page'] ?? 1);
    $perPage = (int) ($_GET['per_page'] ?? 20);
    $controllers['invoice']->list($page, $perPage);
});

$router->add('GET', '/api/invoices/{id}', function(array $params) use ($controllers) {
    $controllers['invoice']->show((int)$params['id']);
});

$router->add('POST', '/api/import', function() use ($controllers) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['file_path'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'file_path is required']);
        return;
    }
    $controllers['invoice']->import($data['file_path']);
});


// Error handlers
$router->addErrorHandler(404, function() {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
});

$router->addErrorHandler(405, function() {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
});

// Dispatch the request
$router->dispatch();
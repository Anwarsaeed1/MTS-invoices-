<?php
require __DIR__ . '/vendor/autoload.php';

use AnwarSaeed\InvoiceProcessor\Commands\ImportCommand;

echo "Testing Invoice Processor Import...\n";

try {
    $command = new ImportCommand();
    $command->execute(['import', 'data.xlsx']);
    echo "Import test completed successfully!\n";
} catch (Exception $e) {
    echo "Import test failed: " . $e->getMessage() . "\n";
} 
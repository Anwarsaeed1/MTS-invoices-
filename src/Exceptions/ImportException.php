<?php

namespace AnwarSaeed\InvoiceProcessor\Exceptions;

use Exception;

class ImportException extends Exception
{
    public function __construct(string $message = "Import failed", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 
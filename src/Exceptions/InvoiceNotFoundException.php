<?php

namespace AnwarSaeed\InvoiceProcessor\Exceptions;

use Exception;

class InvoiceNotFoundException extends Exception
{
    public function __construct(string $message = "Invoice not found", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 
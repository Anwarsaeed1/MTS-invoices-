<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Commands;

interface CommandInterface
{
    /**
     * Execute the command
     */
    public function execute(array $args = []): void;
    
    /**
     * Get command name
     */
    public function getName(): string;
    
    /**
     * Get command description
     */
    public function getDescription(): string;
}

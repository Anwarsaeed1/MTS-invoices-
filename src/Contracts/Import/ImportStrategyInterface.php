<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Import;

interface ImportStrategyInterface
{
    /**
     * Import data from a file
     */
    public function import(string $filePath): array;
    
    /**
     * Check if the strategy can handle the given file
     */
    public function canHandle(string $filePath): bool;
    
    /**
     * Get supported file extensions
     */
    public function getSupportedExtensions(): array;
}

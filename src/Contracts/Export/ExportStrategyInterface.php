<?php

namespace AnwarSaeed\InvoiceProcessor\Contracts\Export;

interface ExportStrategyInterface
{
    /**
     * Export data in the specific format
     */
    public function export(array $data): string;
    
    /**
     * Get the content type for the export
     */
    public function getContentType(): string;
    
    /**
     * Get the file extension for the export
     */
    public function getFileExtension(): string;
}

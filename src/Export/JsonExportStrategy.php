<?php

namespace AnwarSaeed\InvoiceProcessor\Export;

use AnwarSaeed\InvoiceProcessor\Contracts\Export\ExportStrategyInterface;

class JsonExportStrategy implements ExportStrategyInterface
{

    /**
     * Exports an array of data to a JSON string.
     *
     * @param array $data The data to export.
     * @return string The exported JSON string.
     */
    public function export(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Returns the content type for exported JSON data.
     *
     * @return string The content type for exported JSON data.
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
    
    /**
     * Returns the file extension for exported JSON data.
     *
     * @return string The file extension for exported JSON data.
     */
    public function getFileExtension(): string
    {
        return 'json';
    }
}

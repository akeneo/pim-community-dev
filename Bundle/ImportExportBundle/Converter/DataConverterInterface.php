<?php

namespace Oro\Bundle\ImportExportBundle\Converter;

interface DataConverterInterface
{
    /**
     * Convert complex data to export plain format
     *
     * @param array $exportedRecord
     * @return array
     */
    public function convertToExportFormat(array $exportedRecord);

    /**
     * Convert plain data to import complex representation
     *
     * @param array $importedRecord
     * @return array
     */
    public function convertToImportFormat(array $importedRecord);
}

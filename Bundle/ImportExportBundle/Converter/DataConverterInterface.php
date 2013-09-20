<?php

namespace Oro\Bundle\ImportExportBundle\Converter;

interface DataConverterInterface
{
    /**
     * Convert complex data to export plain format
     *
     * @param array $exportedRecord
     * @param boolean $skipNullValues
     * @return array
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true);

    /**
     * Convert plain data to import complex representation
     *
     * @param array $importedRecord
     * @param boolean $skipNullValues
     * @return array
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true);
}

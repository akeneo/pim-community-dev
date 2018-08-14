<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

/**
 * Converts a flat field to a structured format
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldConverterInterface
{
    /**
     * Convert a field from a flat file data (CSV or XLSX file for instance).
     * It guesses its names and its values depending on the data read from the data source.
     *
     * For instance, the category value "cat1,cat2" should be transformed in an array ['cat1', 'cat2']
     *
     * @param string $fieldName
     * @param mixed  $value
     *
     * @return ConvertedField
     */
    public function convert(string $fieldName, $value): ConvertedField;
}

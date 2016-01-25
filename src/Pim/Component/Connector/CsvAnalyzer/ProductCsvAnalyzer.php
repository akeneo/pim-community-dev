<?php

namespace Pim\Component\Connector\CsvAnalyzer;

use Pim\Component\Connector\Reader\File\CsvReader;

/**
 * Provides some statistics on a product CSV file
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvAnalyzer
{
    /** @staticvar string */
    const DEFAULT_DELIMITER = ";";

    /** CsvReader $reader */
    protected $reader;

    /**
     * @param CsvReader $reader
     */
    public function __construct(CsvReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Display statistics about a product CSV file
     *
     * @param string $productCsvFile
     * @param string $delimiter
     *
     * @return array
     */
    public function analyzeCsv($productCsvFile, $delimiter = null)
    {
        $delimiter = (null === $delimiter) ? static::DEFAULT_DELIMITER: $delimiter;

        $stats = [
            "columns_count" => 0,
            "products"      => [
                "count"              => 0,
                "values_count"       => 0,
                "values_per_product" => []
            ],
        ];

        $this->reader->setFilePath($productCsvFile);
        $this->reader->setDelimiter($delimiter);

        $lineNumber = 1;

        while ($valuesData = $this->reader->read()) {
            if (0 === $stats['columns_count']) {
                $stats['columns_count'] = count($valuesData);
            }

            $valuesCount = $this->countValues($valuesData);

            $stats['products']['count']++;
            $stats['products']['values_count'] += $valuesCount;

            $stats['products']['values_per_product'] = $this->computeValuesStats(
                $valuesCount,
                $stats['products']['values_per_product'],
                $lineNumber
            );


            $lineNumber++;
        }

        if ($stats['products']['count'] > 0) {
            $stats['products']['values_per_product']['average'] = round(
                $stats['products']['values_count'] / $stats['products']['count']
            );
        }

        return $stats;
    }

    /**
     * Analyze a CSV line by providing the number of non null values in the line
     *
     * @param array $values
     *
     * @return int
     */
    protected function countValues(array $values)
    {
        $valuesCount = 0;

        foreach ($values as $value) {
            if ("" !== $value && null !== $value) {
                $valuesCount++;
            }
        }

        return $valuesCount;
    }

    /**
     * Compute some stats on product values per product: min, max from the
     * current line valuesCount
     *
     * @param int   $valuesCount
     * @param array $currentStats
     * @param int   $lineNumber
     *
     * @return array
     */
    protected function computeValuesStats($valuesCount, array $currentStats, $lineNumber)
    {
        $valuesStat = [];

        if (!isset($currentStats['min']['count']) || ($valuesCount < $currentStats['min']['count'])) {
            $valuesStat['min'] = [
                'count'       => $valuesCount,
                'line_number' => $lineNumber
            ];
        } else {
            $valuesStat['min'] = $currentStats['min'];
        }

        if (!isset($currentStats['max']['count']) || ($valuesCount > $currentStats['max']['count'])) {
            $valuesStat['max'] = [
                'count'       => $valuesCount,
                'line_number' => $lineNumber
            ];
        } else {
            $valuesStat['max'] = $currentStats['max'];
        }

        return $valuesStat;
    }
}

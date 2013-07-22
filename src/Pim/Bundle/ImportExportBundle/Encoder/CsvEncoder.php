<?php

namespace Pim\Bundle\ImportExportBundle\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * CSV Encoder
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvEncoder implements EncoderInterface
{
    const FORMAT = 'csv';

    protected $delimiter;
    protected $enclosure;
    protected $withHeader = false;

    /**
     * @param string  $delimiter  the field delimiter used in the csv
     * @param string  $enclosure  the field enclosure used in the csv
     * @param boolean $withHeader wether or not to print the columns name
     */
    public function __construct($delimiter = ';', $enclosure = '"', $withHeader = false)
    {
        $this->delimiter  = $delimiter ?: ';';
        $this->enclosure  = $enclosure ?: '"';
        $this->withHeader = $withHeader;
    }

    /**
     * {@inheritDoc}
     */
    public function encode($data, $format)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting data of type array, got "%s".',
                    gettype($data)
                )
            );
        }

        $result = '';
        $output = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            $columns = $this->getColumns($data);
            if ($this->withHeader) {
                $this->encodeHeader($columns, $output);
            }
            foreach ($this->normalizeColumns($data, $columns) as $entry) {
                $this->checkHasStringKeys($entry);
                fputcsv($output, $entry, $this->delimiter, $this->enclosure);
            }
        } else {
            if ($this->withHeader) {
                $this->encodeHeader($data, $output);
            }
            $this->checkHasStringKeys($data);
            fputcsv($output, $data, $this->delimiter, $this->enclosure);
        }

        return $this->readCsv($output);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    private function encodeHeader($data, $output)
    {
        fputcsv($output, array_keys($data), $this->delimiter, $this->enclosure);
    }

    private function readCsv($csvResource)
    {
        rewind($csvResource);
        if (false === $csv = stream_get_contents($csvResource)) {
            throw new \Exception('Error while getting the csv.');
        }
        fclose($csvResource);

        return $csv;
    }

    private function normalizeColumns(array $data, array $columns)
    {
        foreach ($data as $key => $item) {
            $data[$key] = array_merge($columns, $item);
        }

        return $data;
    }

    private function getColumns(array $data)
    {
        $columns = array();

        foreach ($data as $item) {
            foreach (array_keys($item) as $key) {
                $columns[$key] = '';
            }
        }

        return $columns;
    }

    private function checkHasStringKeys(array $data)
    {
        if (empty($data)) {
            return;
        }

        foreach (array_keys($data) as $key) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException(
                    'Expecting string keys.'
                );
            }
        }
    }
}

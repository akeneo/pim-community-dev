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

    protected $firstExecution = true;
    protected $hasHeader      = false;

    /**
     * {@inheritDoc}
     */
    public function encode($data, $format, array $context = array())
    {
        $context = array_merge(
            array(
                'delimiter'     => ';',
                'enclosure'     => '"',
                'withHeader'    => false,
                'heterogeneous' => false,
            ),
            $context
        );
        if (!$this->firstExecution && $context['heterogeneous']) {
            throw new \RuntimeException(
                'The csv encode method should not be called more than once when handling heterogeneous data. '.
                'Otherwise, it won\'t be able to compute the csv columns correctly.'
            );
        }
        $this->firstExecution = false;

        $delimiter  = is_string($context['delimiter']) ? $context['delimiter'] : ';';
        $enclosure  = is_string($context['enclosure']) ? $context['enclosure'] : '"';
        $withHeader = is_bool($context['withHeader']) ? $context['withHeader'] : false;

        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting data of type array, got "%s".',
                    gettype($data)
                )
            );
        }

        $output = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            $columns = $this->getColumns($data);
            if ($withHeader && !$this->hasHeader) {
                $this->encodeHeader($columns, $output, $delimiter, $enclosure);
            }
            foreach ($this->normalizeColumns($data, $columns) as $entry) {
                $this->checkHasStringKeys($entry);
                $this->write($output, $entry, $delimiter, $enclosure);
            }
        } else {
            if ($withHeader && !$this->hasHeader) {
                $this->encodeHeader($data, $output, $delimiter, $enclosure);
            }
            $this->checkHasStringKeys($data);
            $this->write($output, $data, $delimiter, $enclosure);
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

    private function encodeHeader($data, $output, $delimiter, $enclosure)
    {
        $this->write($output, array_keys($data), $delimiter, $enclosure);
        $this->hasHeader = true;
    }

    private function write($output, $entry, $delimiter, $enclosure)
    {
        fputcsv($output, $entry, $delimiter, $enclosure);
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

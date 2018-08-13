<?php

namespace Akeneo\Tool\Component\Connector\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * CSV Encoder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvEncoder implements EncoderInterface
{
    /** @staticvar string */
    const FORMAT = 'csv';

    /** @var bool */
    protected $firstExecution = true;

    /**  @var bool */
    protected $hasHeader = false;

    /** @var string */
    protected $delimiter;

    /** @var string */
    protected $enclosure;

    /** @var string */
    protected $withHeader;

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                sprintf('Expecting data of type array, got "%s".', gettype($data))
            );
        }

        $this->initializeContext($context);

        $output = fopen('php://temp', 'r+b');

        $first = reset($data);
        if (isset($first) && is_array($first)) {
            $columns = $this->getColumns($data);
            if ($this->withHeader && !$this->hasHeader) {
                $this->encodeHeader($columns, $output, $this->delimiter, $this->enclosure);
            }
            foreach ($this->normalizeColumns($data, $columns) as $entry) {
                $this->checkHasStringKeys($entry);
                $this->write($output, $entry, $this->delimiter, $this->enclosure);
            }
        } else {
            if ($this->withHeader && !$this->hasHeader) {
                $this->encodeHeader($data, $output, $this->delimiter, $this->enclosure);
            }
            $this->write($output, $data, $this->delimiter, $this->enclosure);
        }

        $content = $this->readCsv($output);
        fclose($output);

        return $content;
    }

    /**
     * Initialize CSV encoder context merging default configuration
     *
     * @param array $context
     *
     * @throws \RuntimeException
     */
    protected function initializeContext(array $context)
    {
        $context = array_merge($this->getDefaultContext(), $context);

        if (!$this->firstExecution && $context['heterogeneous']) {
            throw new \RuntimeException(
                'The csv encode method should not be called more than once when handling heterogeneous data. '.
                'Otherwise, it won\'t be able to compute the csv columns correctly.'
            );
        }
        $this->firstExecution = false;

        $this->delimiter = is_string($context['delimiter']) ? $context['delimiter'] : ';';
        $this->enclosure = is_string($context['enclosure']) ? $context['enclosure'] : '"';
        $this->withHeader = is_bool($context['withHeader']) ? $context['withHeader'] : false;
    }

    /**
     * Get a default context for the csv encoder
     *
     * @return array
     */
    protected function getDefaultContext()
    {
        return [
            'delimiter'     => ';',
            'enclosure'     => '"',
            'withHeader'    => false,
            'heterogeneous' => false,
        ];
    }

    /**
     * @param array  $data
     * @param mixed  $output
     * @param string $delimiter
     * @param string $enclosure
     */
    private function encodeHeader($data, $output, $delimiter, $enclosure)
    {
        $this->write($output, array_keys($data), $delimiter, $enclosure);
        $this->hasHeader = true;
    }

    /**
     * @param mixed  $output
     * @param array  $entry
     * @param string $delimiter
     * @param string $enclosure
     */
    private function write($output, $entry, $delimiter, $enclosure)
    {
        fputcsv($output, $entry, $delimiter, $enclosure);
    }

    /**
     * @param mixed $csvResource
     *
     * @throws \Exception
     *
     * @return string
     */
    private function readCsv($csvResource)
    {
        rewind($csvResource);

        return stream_get_contents($csvResource);
    }

    /**
     * @param array $data
     * @param array $columns
     *
     * @return array
     */
    private function normalizeColumns(array $data, array $columns)
    {
        foreach ($data as $key => $item) {
            $data[$key] = array_merge($columns, $item);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function getColumns(array $data)
    {
        $columns = [];

        foreach ($data as $item) {
            foreach (array_keys($item) as $key) {
                $columns[$key] = '';
            }
        }

        return $columns;
    }

    /**
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    private function checkHasStringKeys(array $data)
    {
        if (empty($data)) {
            return;
        }

        foreach (array_keys($data) as $key) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException(
                    sprintf('Expecting keys of type "string" but got "%s".', gettype($key))
                );
            }
        }
    }
}

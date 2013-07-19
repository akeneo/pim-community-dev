<?php

namespace Pim\Bundle\ImportExportBundle\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
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

    public function __construct($delimiter = ';', $enclosure = '"', $withHeader = false)
    {
        $this->delimiter  = $delimiter ?: ';';
        $this->enclosure  = $enclosure ?: '"';
        $this->withHeader = $withHeader;
    }

    public function encode($data, $format, array $context = array())
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
            if ($this->withHeader) {
                $this->encodeHeader($data[0], $output);
            }
            foreach ($data as $entry) {
                fputcsv($output, $entry, $this->delimiter, $this->enclosure);
            }
        } else {
            if ($this->withHeader) {
                $this->encodeHeader($data, $output);
            }
            fputcsv($output, $data, $this->delimiter, $this->enclosure);
        }

        return $this->readCsv($output);
    }

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
}

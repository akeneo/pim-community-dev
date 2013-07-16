<?php

namespace Pim\Bundle\ImportExportBundle\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

class CsvEncoder implements EncoderInterface
{
    public function encode($data, $format, array $context = array())
    {
        $rows = join(';', array_keys($data[0]))."\n";
        foreach ($data as $entry) {
            $rows .= join(';', array_values($entry))."\n";
        }

        return $rows;
    }

    public function supportsEncoding($format)
    {
        return 'csv' === $format;
    }
}

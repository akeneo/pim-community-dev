<?php

namespace Pim\Bundle\ImportExportBundle;

use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\Reader\ReaderInterface;
use Pim\Bundle\ImportExportBundle\Writer\WriterInterface;

class Exporter
{
    protected $serializer;
    protected $reader;
    protected $writer;
    protected $format;

    public function __construct(Serializer $serializer, ReaderInterface $reader, WriterInterface $writer, $format)
    {
        $this->serializer = $serializer;
        $this->reader     = $reader;
        $this->writer     = $writer;
        $this->format     = $format;
    }

    public function export()
    {
        $this->writer->write(
            $this->serializer->serialize(
                $this->reader->read(),
                $this->format
            )
        );
    }
}

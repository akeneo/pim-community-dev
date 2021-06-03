<?php

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;

class FileWriterFactory
{
    private array $options;
    private string $type;

    public function __construct(string $type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    public function build(): WriterInterface
    {
        $writer = WriterFactory::create($this->type);
        foreach ($this->options as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($writer, $setter)) {
                $writer->$setter($option);
            } else {
                $message = sprintf('Option "%s" does not exist in writer "%s"', $setter, get_class($writer));
                throw new \InvalidArgumentException($message);
            }
        }

        return $writer;
    }
}
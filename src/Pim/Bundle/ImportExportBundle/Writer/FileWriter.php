<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;

class FileWriter implements ItemWriterInterface
{
    protected $path;
    private $handler;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function write(array $data)
    {
        if (!$this->handler) {
            $this->handler = fopen($this->path, 'w');
        }

        foreach ($data as $entry) {
            fwrite($this->handler, $entry);
        }
    }

    public function __destruct()
    {
        if ($this->handler) {
            fclose($this->handler);
        }
    }
}

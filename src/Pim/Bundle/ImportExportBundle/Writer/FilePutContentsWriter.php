<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

class FilePutContentsWriter implements WriterInterface
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }
    public function write($data, array $options)
    {
        file_put_contents($this->path, $data);
    }
}

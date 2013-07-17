<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\ImportExportBundle\Exception\FileExistsException;

class FilePutContentsWriter implements WriterInterface
{
    protected $path;

    public function __construct($path)
    {
        if (file_exists($path)) {
            throw new FileExistsException($path);
        }
        $this->path = $path;
    }

    public function write($data)
    {
        return file_put_contents($this->path, $data, FILE_APPEND);
    }
}

<?php

namespace Pim\Bundle\BatchBundle\Item\Support;

use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;

class FilePutContentsWriter implements ItemWriterInterface
{
    protected $path;

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function write(array $items)
    {
        foreach ($items as $item) {
            file_put_contents($this->path, $item, FILE_APPEND);
        }
    }
}

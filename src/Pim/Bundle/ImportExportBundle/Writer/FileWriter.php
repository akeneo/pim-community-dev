<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

class FileWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    protected $path;
    private $handler;

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

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getConfigurationFields()
    {
        return array(
            'path' => array(
                'options' => array()
            )
        );
    }
}

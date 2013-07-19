<?php

namespace Pim\Bundle\ImportExportBundle;

use Pim\Bundle\ImportExportBundle\Exporter;

class ExporterRegistry
{
    protected $exporters = array();

    public function registerExporter($alias, Exporter $exporter)
    {
        if ($this->hasExporter($alias)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'An exporter is already defined for alias "%s".',
                    $alias
                )
            );
        }

        $this->exporters[$alias] = $exporter;
    }

    public function getExporters()
    {
        return $this->exporters;
    }

    public function getExporter($alias)
    {
        if (!$this->hasExporter($alias)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No exporter configured with alias "%s".',
                    $alias
                )
            );
        }

        return $this->exporters[$alias];
    }

    private function hasExporter($alias)
    {
        return isset($this->exporters[$alias]);
    }
}

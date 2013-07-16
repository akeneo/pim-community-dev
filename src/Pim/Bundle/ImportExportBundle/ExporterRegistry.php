<?php

namespace Pim\Bundle\ImportExportBundle;

use Pim\Bundle\ImportExportBundle\Exporter;

class ExporterRegistry
{
    protected $exporters = array();

    public function registerExporter($alias, Exporter $exporter)
    {
        $this->exporters[$alias] = $exporter;
    }

    public function getExporters()
    {
        return $this->exporters;
    }

    public function getExporter($alias)
    {
        if (!isset($this->exporters[$alias])) {
            throw new \InvalidArgumentException(sprintf(
                'No exporter configured with alias "%s".', $alias
            ));
        }

        return $this->exporters[$alias];
    }
}

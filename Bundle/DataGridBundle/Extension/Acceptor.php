<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

class Acceptor
{
    /** @var array */
    protected $config;

    /**
     * @param ExtensionVisitorInterface[] $extensions
     * @param DatagridInterface           $grid
     *
     * @return void
     */
    public function acceptDatasourceVisitors(array $extensions, DatagridInterface $grid)
    {
        foreach ($extensions as $extension) {
            $extension->visitDatasource($this->getConfig(), $grid->getDatasource());
        }
    }

    /**
     * @param ExtensionVisitorInterface[] $extensions
     * @param \stdClass                   $result
     *
     * @return void
     */
    public function acceptResult(array $extensions, \stdClass $result)
    {
        foreach ($extensions as $extension) {
            $extension->visitResult($this->getConfig(), $result);
        }
    }

    /**
     * Setter for config
     *
     * @param array $config
     *
     * @return mixed
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $config;
    }

    /**
     * Getter for config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}

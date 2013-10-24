<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

class Acceptor
{
    /** @var array */
    protected $config;

    /** @var array */
    protected $sortedExtensions;

    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * @param DatagridInterface $grid
     *
     * @return void
     */
    public function acceptDatasourceVisitors(DatagridInterface $grid)
    {
        foreach ($this->getSortedExtension($grid) as $extension) {
            $extension->visitDatasource($this->getConfig(), $grid->getDatasource());
        }
    }

    /**
     * @param DatagridInterface $grid
     * @param \stdClass         $result
     *
     * @return void
     */
    public function acceptResult(DatagridInterface $grid, \stdClass $result)
    {
        foreach ($this->getSortedExtension($grid) as $extension) {
            $extension->visitResult($this->getConfig(), $result);
        }
    }

    /**
     * @param DatagridInterface $grid
     * @param \stdClass         $data
     */
    public function acceptMetadata(DatagridInterface $grid, \stdClass $data)
    {
        foreach ($this->getSortedExtension($grid) as $extension) {
            $extension->visitMetadata($this->getConfig(), $data);
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

    /**
     * @param DatagridInterface $grid
     *
     * @return ExtensionVisitorInterface[]
     */
    protected function getSortedExtension(DatagridInterface $grid)
    {
        if (!$this->sortedExtensions) {
            $this->sortedExtensions = $grid->getExtensions();

            usort(
                $this->sortedExtensions,
                function (ExtensionVisitorInterface $a, ExtensionVisitorInterface $b) {
                    if ($a->getPriority() === $b->getPriority()) {
                        return 0;
                    }

                    return $a->getPriority() > $b->getPriority() ? -1 : 1;
                }
            );
        }


        return $this->sortedExtensions;
    }
}

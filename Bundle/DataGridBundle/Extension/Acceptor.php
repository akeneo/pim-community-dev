<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class Acceptor
{
    /** @var DatagridConfiguration */
    protected $config;

    /** @var ExtensionVisitorInterface[] */
    protected $extensions = [];

    public function __construct(DatagridConfiguration $config)
    {
        $this->setConfig($config);
    }

    /**
     * @param DatasourceInterface $datasource
     */
    public function acceptDatasourceVisitors(DatasourceInterface $datasource)
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitDatasource($this->getConfig(), $datasource);
        }
    }

    /**
     * @param ResultsObject $result
     */
    public function acceptResult(ResultsObject $result)
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitResult($this->getConfig(), $result);
        }
    }

    /**
     * @param MetadataObject $data
     */
    public function acceptMetadata(MetadataObject $data)
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitMetadata($this->getConfig(), $data);
        }
    }

    /**
     * Add extension that applicable to datagrid and resort all added extensions
     *
     * @param ExtensionVisitorInterface $extension
     *
     * @return $this
     */
    public function addExtension(ExtensionVisitorInterface $extension)
    {
        /**
         * ATTENTION: extensions should be cloned due to extension object could has state
         */
        $this->extensions[] = clone $extension;

        usort(
            $this->extensions,
            function (ExtensionVisitorInterface $a, ExtensionVisitorInterface $b) {
                if ($a->getPriority() === $b->getPriority()) {
                    return 0;
                }

                return $a->getPriority() > $b->getPriority() ? -1 : 1;
            }
        );

        return $this;
    }

    /**
     * Returns extensions applicable to datagrid
     *
     * @return ExtensionVisitorInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Setter for config
     *
     * @param DatagridConfiguration $config
     *
     * @return mixed
     */
    public function setConfig(DatagridConfiguration $config)
    {
        $this->config = $config;

        return $config;
    }

    /**
     * Getter for config
     *
     * @return DatagridConfiguration
     */
    public function getConfig()
    {
        return $this->config;
    }
}

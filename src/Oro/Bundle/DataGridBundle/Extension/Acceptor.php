<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

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
     * Ask extensions to process configuration
     */
    public function processConfiguration(): void
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->processConfigs($this->getConfig());
        }
    }

    /**
     * @param DatasourceInterface $datasource
     */
    public function acceptDatasource(DatasourceInterface $datasource): void
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitDatasource($this->getConfig(), $datasource);
        }
    }

    /**
     * @param ResultsIterableObject $result
     */
    public function acceptResult(ResultsIterableObject $result): void
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitResult($this->getConfig(), $result);
        }
    }

    /**
     * @param MetadataIterableObject $data
     */
    public function acceptMetadata(MetadataIterableObject $data): void
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
    public function addExtension(ExtensionVisitorInterface $extension): self
    {
        /**
         * ATTENTION: extension object should be cloned cause it can contain some state
         */
        $this->extensions[] = clone $extension;

        $comparisonClosure = function (ExtensionVisitorInterface $a, ExtensionVisitorInterface $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return $a->getPriority() > $b->getPriority() ? -1 : 1;
        };

        // https://bugs.php.net/bug.php?id=50688
        @usort($this->extensions, $comparisonClosure);

        return $this;
    }

    /**
     * Returns extensions applicable to datagrid
     *
     * @return ExtensionVisitorInterface[]
     */
    public function getExtensions(): array
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
    public function setConfig(DatagridConfiguration $config): \Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration
    {
        $this->config = $config;

        return $config;
    }

    /**
     * Getter for config
     */
    public function getConfig(): \Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration
    {
        return $this->config;
    }
}

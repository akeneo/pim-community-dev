<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

class Acceptor
{
    /** @var array */
    protected $config;

    /** @var ExtensionVisitorInterface[] */
    protected $extensions = [];

    public function __construct(array $config)
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
     * @param \stdClass $result
     */
    public function acceptResult(\stdClass $result)
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->visitResult($this->getConfig(), $result);
        }
    }

    /**
     * @param \stdClass $data
     */
    public function acceptMetadata(\stdClass $data)
    {
        foreach ($this->getExtensions() as $extension) {
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
}

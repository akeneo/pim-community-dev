<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

class FormatterExtension implements ExtensionVisitorInterface
{
    /** @var array */
    protected $config;

    /**
     * Add extension to datagrid if needed
     *
     * @param BuildBefore $event
     */
    public function buildBeforeHandler(BuildBefore $event)
    {
        $grid = $event->getDatagrid();
        $config = $event->getConfig();

        if ($this->isApplicable($config)) {
            $grid->addExtension($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        return (!empty($config['columns']) || !empty($config['properties']));
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatasourceInterface $datasource)
    {
        // this extension do not affect source, do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(\stdClass $result)
    {
        $rows = (array)$result->rows;

    }

    protected function getProperties()
    {
        $properties = array();
        if (!empty($this->config['properties'])) {
            foreach ($this->config['properties'] as $propertyConfig) {
                $properties['name'] = new $propertyConfig['type'];
            }
        }
    }


    /**
     * Setter for config
     *
     * @param array $config
     *
     * @return $this
     */
    protected function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }
}

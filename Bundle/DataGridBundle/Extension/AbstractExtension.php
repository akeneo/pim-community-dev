<?php

namespace Oro\Bundle\DataGridBundle\Extension;


use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;

abstract class AbstractExtension implements ExtensionVisitorInterface
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var PropertyAccessor */
    protected $accessor;

    public function __construct(RequestParameters $requestParams = null)
    {
        $this->requestParams = $requestParams;
        $this->accessor      = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        // default priority if not overridden by child
        return 0;
    }

    /**
     * Validate configuration
     *
     * @param ConfigurationInterface      $configuration
     * @param                             $config
     */
    protected function validateConfiguration(ConfigurationInterface $configuration, $config)
    {
        $processor = new Processor();
        $processor->processConfiguration(
            $configuration,
            $config
        );
    }

    /**
     * Getter for request parameters object
     *
     * @return RequestParameters
     */
    protected function getRequestParams()
    {
        return $this->requestParams;
    }
}

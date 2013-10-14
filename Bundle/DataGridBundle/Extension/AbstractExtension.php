<?php

namespace Oro\Bundle\DataGridBundle\Extension;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

abstract class AbstractExtension implements ExtensionVisitorInterface
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var PropertyAccessor */
    protected $accessor;

    public function __construct(RequestParameters $requestParams = null)
    {
        $this->requestParams = $requestParams;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $data)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function visitResult(array $config, \stdClass $result)
    {
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

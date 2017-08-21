<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class MetadataParser
{
    const ROUTE = 'oro_datagrid_index';

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns grid metadata array
     *
     * @param string $name
     * @param array  $params
     *
     * @return array
     */
    public function getGridMetadata(string $name, array $params = []): array
    {
        $metaData = $this->getDatagridManager()->getDatagrid($name)->getMetadata();
        $metaData->offsetAddToArray('options', ['url' => $this->generateUrl($name, $params)]);

        return $metaData->toArray();
    }

    /**
     * Renders grid data using internal request
     * We add additional params form current request to avoid two request on page refresh
     *
     * @param string            $name
     * @param array             $params
     *
     * @return string
     */
    public function getGridData(string $name, array $params = []): string
    {
        return $this->container->get('fragment.handler')->render($this->generateUrl($name, $params));
    }

    /**
     * @param string $name
     * @param array  $params
     * @param bool   $mixRequest
     *
     * @return string
     */
    protected function generateUrl(string $name, array $params): string
    {
        $additional = $this->getRequestParameters()->getRootParameterValue();

        $params = [
            $name      => array_merge($params, $additional),
            'gridName' => $name
        ];

        return $this->getRouter()->generate(self::ROUTE, $params);
    }

    /**
     * @return Manager
     */
    final protected function getDatagridManager(): Manager
    {
        return $this->container->get('oro_datagrid.datagrid.manager');
    }

    /**
     * @return RequestParameters
     */
    final protected function getRequestParameters(): RequestParameters
    {
        return $this->container->get('oro_datagrid.datagrid.request_params');
    }

    /**
     * @return RouterInterface
     */
    final protected function getRouter(): RouterInterface
    {
        return $this->container->get('router');
    }
}

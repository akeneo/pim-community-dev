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
     * @param Manager            $manager
     * @param RequestParameters  $requestParams
     * @param RouterInterface    $router
     */
    public function __construct(
        ContainerInterface $container,
        Manager $manager,
        RequestParameters $requestParams,
        RouterInterface $router
    ) {
        $this->container = $container;
        $this->manager = $manager;
        $this->requestParams = $requestParams;
        $this->router = $router;
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
        $metaData = $this->manager->getDatagrid($name)->getMetadata();
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
        $additional = $this->requestParams->getRootParameterValue();

        $params = [
            $name      => array_merge($params, $additional),
            'gridName' => $name
        ];

        return $this->router->generate(self::ROUTE, $params);
    }
}

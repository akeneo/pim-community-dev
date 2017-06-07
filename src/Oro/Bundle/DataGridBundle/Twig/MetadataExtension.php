<?php

namespace Oro\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class MetadataExtension extends Twig_Extension
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
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_datagrid_metadata';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('oro_datagrid_data', [$this, 'getGridData'], ['needs_environment' => true]),
            new Twig_SimpleFunction('oro_datagrid_metadata', [$this, 'getGridMetadata']),
        ];
    }

    /**
     * Returns grid metadata array
     *
     * @param string $name
     * @param array  $params
     *
     * @return \stdClass
     */
    public function getGridMetadata($name, $params = [])
    {
        $metaData = $this->getDatagridManager()->getDatagrid($name)->getMetadata();
        $metaData->offsetAddToArray('options', ['url' => $this->generateUrl($name, $params)]);

        return $metaData->toArray();
    }

    /**
     * Renders grid data using internal request
     * We add additional params form current request to avoid two request on page refresh
     *
     * @param \Twig_Environment $twig
     * @param string            $name
     * @param array             $params
     *
     * @return mixed
     */
    public function getGridData(\Twig_Environment $twig, $name, $params = [])
    {
        return $twig->getExtension('actions')->renderUri($this->generateUrl($name, $params, true));
    }

    /**
     * @param string $name
     * @param array  $params
     * @param bool   $mixRequest
     *
     * @return string
     */
    protected function generateUrl($name, $params, $mixRequest = false)
    {
        $additional = $mixRequest ? $this->getRequestParameters()->getRootParameterValue() : [];
        $params = [
            $name      => array_merge($params, $additional),
            'gridName' => $name
        ];

        return $this->getRouter()->generate(self::ROUTE, $params);
    }

    /**
     * @return Manager
     */
    final protected function getDatagridManager()
    {
        return $this->container->get('oro_datagrid.datagrid.manager');
    }

    /**
     * @return RequestParameters
     */
    final protected function getRequestParameters()
    {
        return $this->container->get('oro_datagrid.datagrid.request_params');
    }

    /**
     * @return RouterInterface
     */
    final protected function getRouter()
    {
        return $this->container->get('router');
    }
}

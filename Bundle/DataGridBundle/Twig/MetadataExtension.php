<?php

namespace Oro\Bundle\DataGridBundle\Twig;

use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

class MetadataExtension extends \Twig_Extension
{
    const ROUTE = 'oro_datagrid_index';

    /** @var Manager */
    protected $manager;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var RouterInterface */
    protected $router;

    public function __construct(Manager $manager, RequestParameters $requestParams, RouterInterface $router)
    {
        $this->manager       = $manager;
        $this->requestParams = $requestParams;
        $this->router        = $router;
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
            'oro_datagrid_data'     => new \Twig_Function_Method($this, 'getGridData', ['needs_environment' => true]),
            'oro_datagrid_metadata' => new \Twig_Function_Method($this, 'getGridMetadata')
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
        $metaData = $this->manager->getDatagrid($name)->getMetadata();
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
        $additional = $mixRequest ? $this->requestParams->getRootParameterValue() : [];
        $params     = [
            $name      => array_merge($params, $additional),
            'gridName' => $name
        ];

        return $this->router->generate(self::ROUTE, $params);
    }
}

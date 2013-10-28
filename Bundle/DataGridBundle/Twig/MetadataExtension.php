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
     * @param array  $additionalParams
     *
     * @return \stdClass
     */
    public function getGridMetadata($name, $additionalParams = [])
    {
        $metaData = $this->manager->getDatagrid($name)->getMetadata();

        $additionalParams = array_merge([$name => $additionalParams], ['gridName' => $name]);
        $metaData->offsetAddToArray('options', ['url' => $this->router->generate(self::ROUTE, $additionalParams)]);

        return $metaData->toArray();
    }

    /**
     * Renders grid data using internal request
     * We add additional params form current request to avoid two request on page refresh
     *
     * @param \Twig_Environment $twig
     * @param string            $name
     * @param array             $additionalParams
     *
     * @return mixed
     */
    public function getGridData(\Twig_Environment $twig, $name, $additionalParams = [])
    {
        $additionalParams = array_merge(
            [$name => array_merge($additionalParams, $this->requestParams->getRootParameterValue())],
            ['gridName' => $name]
        );

        return $twig->getExtension('actions')->renderUri($this->router->generate(self::ROUTE, $additionalParams));
    }
}

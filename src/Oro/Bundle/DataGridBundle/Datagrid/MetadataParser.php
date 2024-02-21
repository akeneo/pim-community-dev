<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\RouterInterface;

/**
 * Metadata parser for grid data
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetadataParser
{
    const ROUTE = 'oro_datagrid_index';

    /** @var FragmentHandler */
    private $fragmentHandler;

    /** @var Manager */
    private $manager;

    /** @var RequestParameters */
    private $requestParams;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        FragmentHandler $fragmentHandler,
        Manager $manager,
        RequestParameters $requestParams,
        RouterInterface $router
    ) {
        $this->fragmentHandler = $fragmentHandler;
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
        return $this->fragmentHandler->render($this->generateUrl($name, $params));
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

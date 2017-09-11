<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sequential edit controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditController
{
    /** @var MassActionParametersParser */
    protected $parameterParser;

    /** @var GridFilterAdapterInterface */
    protected $filterAdapter;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /**
     * @param MassActionParametersParser          $parameterParser
     * @param GridFilterAdapterInterface          $filterAdapter
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     */
    public function __construct(
        MassActionParametersParser $parameterParser,
        GridFilterAdapterInterface $filterAdapter,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $this->parameterParser      = $parameterParser;
        $this->filterAdapter        = $filterAdapter;
        $this->pqbFactory           = $pqbFactory;
    }

    /**
     * Get ids from datagrid request
     *
     * @return JsonResponse
     */
    public function getIdsAction(Request $request)
    {
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->filterAdapter->adapt($parameters);
        $products = [];
        $cursor = $this->getProductsCursor($filters);
        while ($cursor->valid() && $cursor->key() < 1000) {
            $products[] = $cursor->current();
            $cursor->next();
        }

        return new JsonResponse($products);
    }

    /**
     * @param array            $filters
     * @param ChannelInterface $channel
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters, ChannelInterface $channel = null)
    {
        $options = ['filters' => $filters];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }

        $productQueryBuilder = $this->pqbFactory->create($options);

        return $productQueryBuilder->execute();
    }
}

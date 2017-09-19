<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
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
    private const MAX_PRODUCT_COUNT = 1000;

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
    public function getIdsAction(Request $request): JsonResponse
    {
        $parameters = $this->parameterParser->parse($request);
        $filters = $this->filterAdapter->adapt($parameters);

        $products = [];
        $cursor = $this->getProductsCursor($filters, [
            'locale' => $parameters['dataLocale'],
            'scope'  => $parameters['dataScope'],
            'sort'   => $parameters['sort']
        ]);

        while ($cursor->valid() && $cursor->key() < self::MAX_PRODUCT_COUNT) {
            $products[] = IdEncoder::decode($cursor->current());
            $cursor->next();
        }

        return new JsonResponse(['entities' => $products, 'total' => $cursor->count()]);
    }

    /**
     * @param array            $filters
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters, $context): CursorInterface
    {
        $productQueryBuilder = $this->pqbFactory->create(['filters' => $filters]);
        if (null !== $context['sort']) {
            $field = each($context['sort'])['key'];
            $productQueryBuilder->addSorter($field, $context['sort'][$field], $context);
        }

        return $productQueryBuilder->execute();
    }
}

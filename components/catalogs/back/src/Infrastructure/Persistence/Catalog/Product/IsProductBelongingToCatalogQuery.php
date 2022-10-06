<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsProductBelongingToCatalogQuery implements IsProductBelongingToCatalogQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
    ) {
    }

    public function execute(Catalog $catalog, string $productUuid): bool
    {
        $pqb = $this->productQueryBuilderFactory->create([
            'filters' => $this->getFilters($catalog),
            'limit' => 1,
        ]);
        $pqb->addFilter('id', Operators::EQUALS, $productUuid);
        $results = $pqb->execute();

        return $results->count() !== 0;
    }

    /**
     * @return array<mixed>
     */
    private function getFilters(Catalog $catalog): array
    {
        $filters = [];
        foreach ($catalog->getProductSelectionCriteria() as $criterion) {
            $filter = $criterion;

            if (isset($criterion['scope'])) {
                $filter['context']['scope'] = $criterion['scope'];
            }

            if (isset($criterion['locale'])) {
                $filter['context']['locale'] = $criterion['locale'];
            }

            unset($filter['scope'], $filter['locale']);

            $filters[] = $filter;
        }

        return $filters;
    }
}

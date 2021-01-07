<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Counter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Oro\Bundle\PimDataGridBundle\Adapter\ItemsCounter;

class PublishedProductItemsCounter extends ItemsCounter
{
    private const PUBLISHED_PRODUCT_GRID_NAME = 'published-product-grid';

    /** @var ProductQueryBuilderFactoryInterface */
    private $publishedProductQueryBuilderFactory;

    public function __construct(
        CountImpactedProducts $countImpactedProducts,
        ProductQueryBuilderFactoryInterface $publishedProductQueryBuilderFactory
    ) {
        parent::__construct($countImpactedProducts);
        $this->publishedProductQueryBuilderFactory = $publishedProductQueryBuilderFactory;
    }

    public function count(string $gridName, array $filters): int
    {
        if ($gridName === self::PUBLISHED_PRODUCT_GRID_NAME) {
            $pqb = $this->publishedProductQueryBuilderFactory->create(['filters' => $filters]);

            return $pqb->execute()->count();
        }

        return parent::count($gridName, $filters);
    }
}

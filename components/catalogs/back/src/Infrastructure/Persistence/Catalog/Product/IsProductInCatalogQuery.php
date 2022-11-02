<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsProductInCatalogQuery
{
    private ProductQueryBuilderFactoryInterface $factory;

    public function __construct(
        ProductQueryBuilderFactoryInterface $factory,
    ) {
        $this->factory = $factory;
    }

    public function execute(Catalog $catalog, string $productUuid): bool
    {
        $pqb = $this->factory->create([
            'filters' => $catalog->getProductQueryBuilderFilters(),
            'limit' => 1,
        ]);

        $pqb->addFilter('id', Operators::EQUALS, $productUuid);

        return $pqb->execute()->count() === 1;
    }
}

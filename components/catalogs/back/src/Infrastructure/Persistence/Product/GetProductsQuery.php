<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Product\GetProductsQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductsQuery implements GetProductsQueryInterface
{
    public function __construct(
        private GetProductUuidsQueryInterface $getProductUuidsQuery,
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array {
        $uuids = $this->getProductUuidsQuery->execute($catalog, $searchAfter, $limit, $updatedAfter, $updatedBefore);

        return $this->productRepository->getItemsFromUuids($uuids);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsWithFilteredValuesQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsWithFilteredValuesQueryInterface
 */
final class GetProductsHandler
{
    public function __construct(
        private GetProductsWithFilteredValuesQueryInterface $getProductsQuery,
        private GetCatalogQueryInterface $getCatalogQuery,
    ) {
    }

    /**
     * @return array<Product>
     *
     * @throws ServiceApiCatalogNotFoundException
     */
    public function __invoke(GetProductsQuery $query): array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        return $this->getProductsQuery->execute(
            $catalog,
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );
    }
}

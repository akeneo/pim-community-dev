<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductQueryInterface
 */
final class GetProductHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private IsProductBelongingToCatalogQueryInterface $isProductBelongingToCatalogQuery,
        private GetProductQueryInterface $getProductQuery,
    ) {
    }

    /**
     * @return Product|null
     * @throws ServiceApiCatalogNotFoundException
     */
    public function __invoke(GetProductQuery $query): ?array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        $productUuid = $query->getProductUuid();

        if (!$this->isProductBelongingToCatalogQuery->execute($catalog, $productUuid)) {
            return null;
        }

        return $this->getProductQuery->execute($catalog, $productUuid);
    }
}

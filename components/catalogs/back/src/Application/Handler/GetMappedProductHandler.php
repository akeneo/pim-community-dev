<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Service\ProductMapperInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
final class GetMappedProductHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private GetRawProductQueryInterface $getRawProductQuery,
        private ProductMapperInterface $productMapper,
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
        private IsProductBelongingToCatalogQueryInterface $isProductBelongingToCatalogQuery,
    ) {
    }

    /**
     * @return MappedProduct
     */
    public function __invoke(GetMappedProductQuery $query): array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        if (!$catalog->isEnabled()) {
            throw new CatalogDisabledException();
        }

        if (!$this->isProductBelongingToCatalogQuery->execute($catalog, $query->getProductUuid())) {
            throw new ProductNotFoundException();
        }

        $product = $this->getRawProductQuery->execute($query->getProductUuid());

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalog->getId());
        $productMapping = $catalog->getProductMapping();

        /** @var RawProduct $product */
        return $this->productMapper->getMappedProduct($product, $productMappingSchema, $productMapping);
    }
}

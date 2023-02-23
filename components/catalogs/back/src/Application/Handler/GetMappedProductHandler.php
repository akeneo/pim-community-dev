<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ProductMapperInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductMappingSchemaNotFoundException as ServiceApiProductMappingSchemaNotFoundException;
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
        private DisableCatalogQueryInterface $disableCatalogQuery,
        private IsCatalogValidInterface $isCatalogValid,
        private DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
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

        try {
            if (!$this->isProductBelongingToCatalogQuery->execute($catalog, $query->getProductUuid())) {
                throw new ProductNotFoundException();
            }

            $product = $this->getRawProductQuery->execute($query->getProductUuid());
        } catch (\Exception $exception) {
            if (!($this->isCatalogValid)($catalog)) {
                $this->disableCatalogQuery->execute($catalog->getId());
                ($this->dispatchInvalidCatalogDisabledEvent)($catalog->getId());
                throw new CatalogDisabledException(previous: $exception);
            }

            throw $exception;
        }

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        try {
            $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalog->getId());
        } catch (ProductMappingSchemaNotFoundException) {
            throw new ServiceApiProductMappingSchemaNotFoundException();
        }

        $productMapping = $catalog->getProductMapping();

        /** @var RawProduct $product */
        return $this->productMapper->getMappedProduct($product, $productMappingSchema, $productMapping);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Exception\ProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ProductMapperInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetProductCategoriesLabelsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductMappingSchemaNotFoundException as ServiceApiProductMappingSchemaNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 * @phpstan-import-type ProductMapping from Catalog
 */
final class GetMappedProductsHandler
{
    public function __construct(
        private readonly GetCatalogQueryInterface $getCatalogQuery,
        private readonly GetRawProductsQueryInterface $getRawProductsQuery,
        private readonly DisableCatalogQueryInterface $disableCatalogQuery,
        private readonly IsCatalogValidInterface $isCatalogValid,
        private readonly DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
        private ProductMapperInterface $productMapper,
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
        private readonly GetProductCategoriesLabelsQueryInterface $getProductCategoriesLabelsQuery,
    ) {
    }

    /**
     * @return array<array-key, MappedProduct>
     */
    public function __invoke(GetMappedProductsQuery $query): array
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
            $products = $this->getRawProductsQuery->execute(
                $catalog,
                $query->getSearchAfter(),
                $query->getLimit(),
                $query->getUpdatedAfter(),
                $query->getUpdatedBefore(),
            );
        } catch (\Exception $exception) {
            if (!($this->isCatalogValid)($catalog)) {
                $this->disableCatalogQuery->execute($catalog->getId());
                ($this->dispatchInvalidCatalogDisabledEvent)($catalog->getId());
                throw new CatalogDisabledException(previous: $exception);
            }
            throw $exception;
        }

        try {
            $productMappingSchema = $this->getProductMappingSchemaQuery->execute($catalog->getId());
        } catch (ProductMappingSchemaNotFoundException) {
            throw new ServiceApiProductMappingSchemaNotFoundException();
        }

        $productMapping = $catalog->getProductMapping();

        $this->warmupProductCategoryCache($productMapping, \array_column($products, 'uuid'));

        return \array_map(
            /** @param RawProduct $product */
            fn (array $product): array => $this->productMapper->getMappedProduct($product, $productMappingSchema, $productMapping),
            $products,
        );
    }

    /**
     * @param ProductMapping $productMapping
     * @param array<UuidInterface> $productUuids
     */
    private function warmupProductCategoryCache(array $productMapping, array $productUuids): void
    {
        if (!\method_exists($this->getProductCategoriesLabelsQuery, 'warmup')) {
            return;
        }

        $categoryLocales = [];
        foreach ($productMapping as $targetSourceAssociation) {
            if ('categories' === $targetSourceAssociation['source']
                && isset($targetSourceAssociation['parameters']['label_locale'])
                && \is_string($targetSourceAssociation['parameters']['label_locale'])
                && $targetSourceAssociation['parameters']['label_locale'] !== ''
                && !\in_array($targetSourceAssociation['parameters']['label_locale'], $categoryLocales)
            ) {
                $categoryLocales[] = $targetSourceAssociation['parameters']['label_locale'];
            }
        }

        $this->getProductCategoriesLabelsQuery->warmup(
            \array_map(static fn (UuidInterface $uuid): string => $uuid->toString(), $productUuids),
            $categoryLocales,
        );
    }
}

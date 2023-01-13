<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetAttributeTypeByCodesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Application\Service\MapAttributeServiceInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 */
final class GetMappedProductsHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
        private GetRawProductsQueryInterface $getRawProductsQuery,
        private GetAttributeTypeByCodesQueryInterface $getAttributeTypeByCodesQuery,
        private GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
        private MapAttributeServiceInterface $mapAttributeService,
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

        $products = $this->getRawProductsQuery->execute(
            $catalog,
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );

        return $this->mapProducts($products, $catalog);
    }

    /**
     * @param array<RawProduct> $products
     *
     * @return array<MappedProduct>
     */
    private function mapProducts(array $products, Catalog $catalog): array
    {
        $productMappingSchema = $this->getProductMappingSchema($catalog->getId());
        $productMapping = $catalog->getProductMapping();

        $attributeTypeBySource = $this->getAttributeTypeByCodesQuery->execute(\array_column($productMapping, 'source'));

        return \array_map(
            /** @param RawProduct $product */
            function (array $product) use ($attributeTypeBySource, $productMappingSchema, $productMapping): array {
                $mappedProduct = [];

                /** @var string $target */
                foreach (\array_keys($productMappingSchema['properties']) as $target) {
                    $sourceValue = null;

                    if ('uuid' === $target) {
                        $sourceValue = $product['uuid']->toString();
                    } elseif (\array_key_exists($target, $productMapping)) {
                        if ($productMapping[$target]['source'] !== null) {
                            $sourceValue = $this->mapAttributeService->execute(
                                $attributeTypeBySource[$productMapping[$target]['source']],
                                $product,
                                $productMapping[$target],
                            );
                        }
                    }

                    if ($sourceValue !== null) {
                        $mappedProduct[$target] = $sourceValue;
                    }
                }

                return $mappedProduct;
            },
            $products
        );
    }

    /**
     * @return array{
     *      properties: array<array-key, mixed>
     * }
     */
    private function getProductMappingSchema(string $catalogId): array
    {
        $productMappingSchemaFile = \sprintf('%s_product.json', $catalogId);

        if (!$this->catalogsMappingStorage->exists($productMappingSchemaFile)) {
            throw new ServiceApiProductSchemaMappingNotFoundException();
        }

        $productMappingSchemaRaw = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile)
        );

        if (false === $productMappingSchemaRaw) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        /**
         * @var array{
         *      properties: array<array-key, mixed>
         * } $productMappingSchema
         */
        $productMappingSchema = \json_decode($productMappingSchemaRaw, true, 512, JSON_THROW_ON_ERROR);

        return $productMappingSchema;
    }
}

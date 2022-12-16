<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException as ServiceApiProductNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
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
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
        private GetRawProductQueryInterface $getRawProductQuery,
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

        $product = $this->getRawProductQuery->execute($query->getProductUuid());

        if (null === $product) {
            throw new ServiceApiProductNotFoundException();
        }

        return $this->mapProduct($product, $catalog);
    }

    /**
     * @param RawProduct $product
     *
     * @return MappedProduct
     */
    private function mapProduct(array $product, Catalog $catalog): array
    {
        $productMappingSchema = $this->getProductMappingSchema($catalog->getId());
        $productMapping = $catalog->getProductMapping();

        $mappedProduct = [];

        /** @var string $target */
        foreach (\array_keys($productMappingSchema['properties']) as $target) {
            $sourceValue = '';


            if ('uuid' === $target) {
                $sourceValue = $product['uuid']->toString();
            } elseif (\array_key_exists($target, $productMapping)) {
                $sourceValue = $this->getProductAttributeValue(
                    $product,
                    $productMapping[$target]['source'],
                    $productMapping[$target]['locale'],
                    $productMapping[$target]['scope']
                );
            }

            $mappedProduct[$target] = $sourceValue;
        }

        return $mappedProduct;
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

    /**
     * @param RawProduct $product
     */
    private function getProductAttributeValue(array $product, ?string $attributeCode, ?string $locale, ?string $scope): string
    {
        $scope ??= '<all_channels>';
        $locale ??= '<all_locales>';

        return $product['raw_values'][$attributeCode][$scope][$locale] ?? '';
    }
}

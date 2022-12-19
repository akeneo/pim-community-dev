<?php

declare(strict_types=1);


namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Application\Service\MapProductsInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 */
class MapProducts implements MapProductsInterface
{
    public function __construct(
        private readonly CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function __invoke(array $products, Catalog $catalog): array
    {
        $productMappingSchema = $this->getProductMappingSchema($catalog->getId());
        $productMapping = $catalog->getProductMapping();

        return \array_map(
            /** @param RawProduct $product */
            function (array $product) use ($productMappingSchema, $productMapping): array {
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

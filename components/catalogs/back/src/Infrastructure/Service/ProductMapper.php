<?php

declare(strict_types=1);


namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Application\Service\ProductMapperInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 * @phpstan-import-type ProductMapping from Catalog
 */
class ProductMapper implements ProductMapperInterface
{
    /**
     * @param RawProduct $product
     * @param array{properties: array<array-key, mixed>} $productMappingSchema
     * @param ProductMapping $productMapping
     *
     * @return MappedProduct
     */
    public function getMappedProduct(
        array $product,
        array $productMappingSchema,
        array $productMapping
    ): array {
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
     * @param RawProduct $product
     */
    private function getProductAttributeValue(array $product, ?string $attributeCode, ?string $locale, ?string $scope): string
    {
        $scope ??= '<all_channels>';
        $locale ??= '<all_locales>';

        return $product['raw_values'][$attributeCode][$scope][$locale] ?? '';
    }
}

<?php

declare(strict_types=1);


namespace Akeneo\Catalogs\Infrastructure\Mapping;

use Akeneo\Catalogs\Application\Mapping\ProductMapperInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetAttributeTypeByCodesQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 * @phpstan-import-type ProductMapping from Catalog
 */
class ProductMapper implements ProductMapperInterface
{
    public function __construct(
        private readonly GetAttributeTypeByCodesQuery $getAttributeTypeByCodesQuery,
        private readonly ProductValueExtractorRegistry $productValueExtractorRegistry,
    ) {
    }

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

        /** @var array<string> $sourceAttributeCodes */
        $sourceAttributeCodes = \array_filter(\array_column($productMapping, 'source'));
        $attributeTypeBySource = $this->getAttributeTypeByCodesQuery->execute($sourceAttributeCodes);

        /** @var string $targetCode */
        foreach ($productMappingSchema['properties'] as $targetCode => $target) {
            $sourceValue = null;

            if ('uuid' === $targetCode) {
                $sourceValue = $product['uuid']->toString();
            } elseif (\array_key_exists($targetCode, $productMapping) &&
                $productMapping[$targetCode]['source'] !== null &&
                \array_key_exists($productMapping[$targetCode]['source'], $attributeTypeBySource)) {
                try {
                    $sourceValue = $this->productValueExtractorRegistry->extract(
                        $product,
                        $productMapping[$targetCode]['source'],
                        $attributeTypeBySource[$productMapping[$targetCode]['source']],
                        $target['type'],
                        $target['format'] ?? null,
                        $productMapping[$targetCode]['locale'] ?? '<all_locales>',
                        $productMapping[$targetCode]['scope'] ?? '<all_channels>',
                        $productMapping[$targetCode]['parameters'] ?? null,
                    );
                } catch (ProductValueExtractorNotFoundException) {
                }
            }

            if ($sourceValue !== null) {
                $mappedProduct[$targetCode] = $sourceValue;
            }
        }

        return $mappedProduct;
    }
}

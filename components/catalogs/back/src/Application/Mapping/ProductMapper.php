<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry\ValueExtractorRegistry;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetAttributeTypeByCodesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
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
        private readonly GetAttributeTypeByCodesQueryInterface $getAttributeTypeByCodesQuery,
        private readonly ValueExtractorRegistry $valueExtractorRegistry,
    ) {
    }

    /**
     * @param RawProduct $product
     * @param array{properties: array<array-key, mixed>} $productMappingSchema
     * @param ProductMapping $productMapping
     *
     * @return MappedProduct
     *
     * @psalm-suppress MixedAssignment
     */
    public function getMappedProduct(
        array $product,
        array $productMappingSchema,
        array $productMapping,
    ): array {
        $mappedProduct = [];

        /** @var array<string> $sourceAttributeCodes */
        $sourceAttributeCodes = \array_filter(\array_column($productMapping, 'source'));
        $attributeTypeBySource = $this->getAttributeTypeByCodesQuery->execute($sourceAttributeCodes);

        /**
         * @var string $targetCode
         * @var array{type: string, format?: string} $target
         */
        foreach ($productMappingSchema['properties'] as $targetCode => $target) {
            $sourceValue = $this->extractSourceValue($product, $productMapping, $targetCode, $target, $attributeTypeBySource);

            if ($sourceValue !== null) {
                $mappedProduct[$targetCode] = $sourceValue;
            }
        }

        return $mappedProduct;
    }

    /**
     * @param RawProduct $product
     * @param ProductMapping $productMapping
     * @param array{type: string, format?: string} $target
     * @param array<string, string> $attributeTypeBySource
     */
    private function extractSourceValue(
        array $product,
        array $productMapping,
        string $targetCode,
        array $target,
        array $attributeTypeBySource,
    ): mixed {
        if ('uuid' === $targetCode) {
            return $product['uuid']->toString();
        }

        if (!\array_key_exists($targetCode, $productMapping)
            || $productMapping[$targetCode]['source'] === null
        ) {
            return null;
        }

        try {
            $productValueExtractor = $this->valueExtractorRegistry->find(
                $attributeTypeBySource[$productMapping[$targetCode]['source']] ?? $productMapping[$targetCode]['source'],
                $target['type'],
                $target['format'] ?? null,
            );

            return $productValueExtractor->extract(
                $product,
                $productMapping[$targetCode]['source'],
                $productMapping[$targetCode]['locale'] ?? '<all_locales>',
                $productMapping[$targetCode]['scope'] ?? '<all_channels>',
                $productMapping[$targetCode]['parameters'] ?? null,
            );
        } catch (ValueExtractorNotFoundException) {
            return null;
        }
    }
}

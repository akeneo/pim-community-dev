<?php

declare(strict_types=1);


namespace Akeneo\Catalogs\Infrastructure\Service;

use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Catalogs\Application\Service\ProductMapperInterface;
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
        private readonly GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
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

        $attributeTypeBySource = $this->getAttributeTypeByCodesQuery->execute(\array_column($productMapping, 'source'));

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

                if (isset($attributeTypeBySource[$productMapping[$target]['source']]) &&
                    $attributeTypeBySource[$productMapping[$target]['source']] === 'pim_catalog_simpleselect' &&
                    $sourceValue !== '') {
                    $locale = $productMapping[$target]['locale'] ?? 'en_US';
                    $sourceValue = $this->getSimpleSelectLabel($productMapping[$target]['source'], $sourceValue, $locale);
                }
            }

            if ($sourceValue !== null) {
                $mappedProduct[$target] = $sourceValue;
            }
        }

        return $mappedProduct;
    }

    /**
     * @param RawProduct $product
     */
    private function getProductAttributeValue(array $product, ?string $attributeCode, ?string $locale, ?string $scope): string | null
    {
        $scope ??= '<all_channels>';
        $locale ??= '<all_locales>';

        return $product['raw_values'][$attributeCode][$scope][$locale] ?? null;
    }

    private function getSimpleSelectLabel(string $attributeCode, $optionCode, string $locale): string | null
    {
        $options = $this->getAttributeOptionsByCodeQuery->execute($attributeCode, [$optionCode], $locale);

        if (!empty($options)) {
            return $options[0]['label'];
        } else {
            return null;
        }
    }
}

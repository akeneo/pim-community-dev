<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\PqbFilters;

use Akeneo\Catalogs\Application\Exception\ProductMappingRequiredSourceMissingException;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Domain\Operator;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type PqbFilter from PqbFiltersInterface
 * @phpstan-import-type ProductMapping from Catalog
 * @phpstan-import-type ProductMappingSchema from GetProductMappingSchemaQueryInterface
 */
final class ProductMappingRequiredFilters implements PqbFiltersInterface
{
    /**
     * @param ProductMapping $productMapping
     * @param ProductMappingSchema $productMappingSchema
     * @return array<array-key, PqbFilter>
     * @throws ProductMappingRequiredSourceMissingException
     */
    public static function toPQBFilters(array $productMapping, array $productMappingSchema): array
    {
        if (!isset($productMappingSchema['required'])) {
            return [];
        }

        $filters = [];

        foreach ($productMappingSchema['required'] as $targetCode) {
            if ('uuid' === $targetCode) {
                continue;
            }

            if (!isset($productMapping[$targetCode]) || $productMapping[$targetCode]['source'] === null) {
                throw new ProductMappingRequiredSourceMissingException();
            }

            $filter = [
                'field' => $productMapping[$targetCode]['source'],
                'value' => '',
                'operator' => Operator::IS_NOT_EMPTY,
            ];

            $context = [];

            if (null !== $productMapping[$targetCode]['scope']) {
                $context['scope'] = $productMapping[$targetCode]['scope'];
            }

            if (null !== $productMapping[$targetCode]['locale']) {
                $context['locale'] = $productMapping[$targetCode]['locale'];
            }

            if (\count($context) > 0) {
                $filter['context'] = $context;
            }

            $filters[] = $filter;
        }

        return $filters;
    }
}

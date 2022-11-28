<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetPQBFilters
{
    /**
     * @param array<
     *     array-key,
     *     array{field: string, operator: string, value?: mixed, scope?: string|null, locale?: string|null}
     * > $productSelectionCriteria
     *
     * @return array<
     *     array-key,
     *     array{
     *         field: string,
     *         operator: string,
     *         value?: mixed,
     *         context?: array{
     *             scope?: string,
     *             locale?: string,
     *         }
     *     }
     * > $productSelectionCriteria
     */
    public static function fromProductSelectionCriteria(array $productSelectionCriteria): array
    {
        $filters = [];
        foreach ($productSelectionCriteria as $criterion) {
            $filter = $criterion;

            if (isset($criterion['scope'])) {
                $filter['context']['scope'] = $criterion['scope'];
            }

            if (isset($criterion['locale'])) {
                $filter['context']['locale'] = $criterion['locale'];
            }

            unset($filter['scope'], $filter['locale']);

            $filters[] = $filter;
        }

        return $filters;
    }
}

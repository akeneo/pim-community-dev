<?php

namespace Akeneo\Catalogs\Infrastructure\PqbFilters;

/**
 * @phpstan-type PqbFilter array{
 *     field: string,
 *     operator: string,
 *     value?: mixed,
 *     context?: array{
 *         scope?: string,
 *         locale?: string,
 *     }
 * }
 *
 * @method static array<array-key, PqbFilter> toPQBFilters()
 */
interface PqbFiltersInterface
{
}

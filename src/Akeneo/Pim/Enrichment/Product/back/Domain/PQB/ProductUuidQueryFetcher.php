<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\PQB;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUuidQueryFetcher
{
    /**
     * Initialize the fetcher with a query. If a query was in progress it resets it.
     *
     * @param array<mixed> $query
     */
    public function initialize(array $query): void;

    public function getNextResults(): ProductResults;

    public function reset(): void;
}

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
     * @param array<mixed> $esQuery
     */
    public function initialize(array $esQuery): void;

    public function getNextResults(): ProductResults;

    public function reset(): void;
}

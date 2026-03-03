<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\PQB;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductQueryBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildQuery(?int $userId, ?UuidInterface $searchAfterUuid = null): array;
}

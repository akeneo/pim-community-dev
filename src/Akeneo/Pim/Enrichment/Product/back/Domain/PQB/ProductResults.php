<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\PQB;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductResults
{
    /**
     * @param UuidInterface[] $uuids
     */
    public function __construct(private array $uuids, private int $count)
    {
    }

    /**
     * @return UuidInterface[]
     */
    public function uuids(): array
    {
        return $this->uuids;
    }

    public function count(): int
    {
        return $this->count;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetNonViewableCategoryCodes
{
    /**
     * @param UuidInterface[] $productUuids
     * @return array<string, string[]> example:
     *  {
     *      "uuid1": ["categoryA", "categoryB"],
     *      "uuid2": ["categoryA"],
     *      ...
     *  }
     */
    public function fromProductUuids(array $productUuids, int $userId): array;
}

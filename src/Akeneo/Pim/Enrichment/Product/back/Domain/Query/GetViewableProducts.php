<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Query;

use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetViewableProducts
{
    /**
     * @param array<string> $productIdentifiers
     * @param int $userId
     * @return array<string>
     */
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array;

    /**
     * @param array<Uuid> $productUuids
     * @param int $userId
     * @return array<Uuid>
     */
    public function fromProductUuids(array $productUuids, int $userId): array;
}

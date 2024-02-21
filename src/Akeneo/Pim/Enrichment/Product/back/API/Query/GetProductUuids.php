<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductUuids
{
    public function fromIdentifier(string $identifier): ?UuidInterface;

    /**
     * @param string[] $identifiers
     * @return array<string, UuidInterface>
     */
    public function fromIdentifiers(array $identifiers): array;

    /**
     * checks that a given uuid is assigned to an existing product
     */
    public function fromUuid(UuidInterface $uuid): ?UuidInterface;

    /**
     * checks which uuids are assigned to existing products
     * @param UuidInterface[] $uuids
     *
     * @return array<string, UuidInterface>
     */
    public function fromUuids(array $uuids): array;
}

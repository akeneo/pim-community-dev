<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWasUpdated
{
    public function __construct(
        private readonly UuidInterface $productUuid,
        private readonly \DateTimeImmutable $updatedAt
    ) {
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

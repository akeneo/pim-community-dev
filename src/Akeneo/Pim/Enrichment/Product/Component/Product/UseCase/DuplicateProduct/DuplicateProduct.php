<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Ramsey\Uuid\UuidInterface;

final class DuplicateProduct
{
    public function __construct(
        private UuidInterface $productToDuplicateUuid,
        private ?string $duplicatedProductIdentifier,
        private int $userId
    ) {
    }

    public function productToDuplicateUuid(): UuidInterface
    {
        return $this->productToDuplicateUuid;
    }

    public function duplicatedProductIdentifier(): ?string
    {
        return $this->duplicatedProductIdentifier;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

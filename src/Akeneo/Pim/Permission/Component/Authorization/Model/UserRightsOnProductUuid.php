<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Authorization\Model;

use Ramsey\Uuid\UuidInterface;

final class UserRightsOnProductUuid
{
    public function __construct(
        private UuidInterface $productUuid,
        private int $userId,
        private int $numberOfEditableCategories,
        private int $numberOfOwnableCategories,
        private int $numberOfViewableCategories,
        private int $numberOfCategories
    ) {
    }

    public function canApplyDraftOnProduct(): bool
    {
        return $this->numberOfEditableCategories > 0 && 0 === $this->numberOfOwnableCategories;
    }

    public function isProductEditable(): bool
    {
        return $this->numberOfOwnableCategories > 0 || 0 === $this->numberOfCategories;
    }

    public function isProductViewable(): bool
    {
        return $this->numberOfViewableCategories > 0 || 0 === $this->numberOfCategories;
    }

    public function productUuid(): UuidInterface
    {
        return $this->productUuid;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

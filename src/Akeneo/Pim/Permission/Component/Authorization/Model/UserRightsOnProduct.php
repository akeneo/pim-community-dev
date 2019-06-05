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

final class UserRightsOnProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var int */
    private $userId;

    /** @var int */
    private $numberOfEditableCategories;

    /** @var int */
    private $numberOfOwnableCategories;

    /** @var int */
    private $numberOfViewableCategories;

    /** @var int */
    private $numberOfCategories;

    /**
     * @param string $productIdentifier
     * @param int $userId
     * @param int $numberOfEditableCategories
     * @param int $numberOfOwnableCategories
     * @param int $numberOfViewableCategories
     * @param int $numberOfCategories
     */
    public function __construct(
        string $productIdentifier,
        int $userId,
        int $numberOfEditableCategories,
        int $numberOfOwnableCategories,
        int $numberOfViewableCategories,
        int $numberOfCategories
    ) {
        $this->productIdentifier = $productIdentifier;
        $this->userId = $userId;
        $this->numberOfEditableCategories = $numberOfEditableCategories;
        $this->numberOfOwnableCategories = $numberOfOwnableCategories;
        $this->numberOfViewableCategories = $numberOfViewableCategories;
        $this->numberOfCategories = $numberOfCategories;
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

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

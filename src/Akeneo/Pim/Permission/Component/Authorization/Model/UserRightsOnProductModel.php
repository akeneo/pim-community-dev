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

final class UserRightsOnProductModel
{
    /** @var string */
    private $productModelCode;

    /** @var int */
    private $userId;

    /** @var int */
    private $numberOfEditableCategories;

    /** @var int */
    private $numberOfOwnableCategories;

    /** @var int */
    private $numberOfCategories;

    /**
     * @param string $productModelCode
     * @param int    $userId
     * @param int    $numberOfEditableCategories
     * @param int    $numberOfOwnableCategories
     * @param int    $numberOfCategories
     */
    public function __construct(
        string $productModelCode,
        int $userId,
        int $numberOfEditableCategories,
        int $numberOfOwnableCategories,
        int $numberOfCategories
    ) {
        $this->productModelCode = $productModelCode;
        $this->userId = $userId;
        $this->numberOfEditableCategories = $numberOfEditableCategories;
        $this->numberOfOwnableCategories = $numberOfOwnableCategories;
        $this->numberOfCategories = $numberOfCategories;
    }

    public function canApplyDraftOnProductModel(): bool
    {
        return $this->numberOfEditableCategories > 0 && 0 === $this->numberOfOwnableCategories;
    }

    public function isProductModelEditable(): bool
    {
        return $this->numberOfOwnableCategories > 0 || 0 === $this->numberOfCategories;
    }

    public function productModelCode(): string
    {
        return $this->productModelCode;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

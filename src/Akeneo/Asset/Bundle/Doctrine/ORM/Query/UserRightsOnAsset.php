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

namespace Akeneo\Asset\Bundle\Doctrine\ORM\Query;

final class UserRightsOnAsset
{
    /** @var string */
    private $assetCode;

    /** @var int */
    private $userId;

    /** @var int */
    private $numberOfEditableCategories;

    /**
     * @param string $assetCode
     * @param int    $userId
     * @param int    $numberOfEditableCategories
     */
    public function __construct(
        string $assetCode,
        int $userId,
        int $numberOfEditableCategories
    ) {
        $this->assetCode = $assetCode;
        $this->userId = $userId;
        $this->numberOfEditableCategories = $numberOfEditableCategories;
    }

    public function isAssetEditable(): bool
    {
        return $this->numberOfEditableCategories > 0;
    }

    public function assetCode(): string
    {
        return $this->assetCode;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}

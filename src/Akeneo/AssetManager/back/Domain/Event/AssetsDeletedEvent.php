<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Contracts\EventDispatcher\Event;
use Webmozart\Assert\Assert;

/**
 * Event triggered when multiple assets are deleted from DB
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class AssetsDeletedEvent extends Event
{
    /**
     * @param AssetCode[] $assetCodes
     */
    public function __construct(
        private AssetFamilyIdentifier $assetFamilyIdentifier,
        private array $assetCodes
    ) {
        Assert::allIsInstanceOf($assetCodes, AssetCode::class);
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    /**
     * @return AssetCode[]
     */
    public function getAssetCodes(): array
    {
        return $this->assetCodes;
    }
}

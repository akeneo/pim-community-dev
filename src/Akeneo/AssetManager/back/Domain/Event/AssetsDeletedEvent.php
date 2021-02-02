<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered when multiple assets are deleted from DB
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class AssetsDeletedEvent extends Event
{
    private AssetFamilyIdentifier $assetFamilyIdentifier;

    /** @var AssetCode[] */
    private array $assetCodes;

    public function __construct(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $assetCodes
    ) {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->assetCodes = $assetCodes;
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

<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
class AssetCreatedEvent extends Event implements DomainEvent
{
    /** @var AssetIdentifier */
    private $assetIdentifier;

    /** @var AssetCode */
    private $assetCode;

    /** @var AssetFamilyIdentifier */
    private $assetFamilyIdentifier;

    public function __construct(
        AssetIdentifier $assetIdentifier,
        AssetCode $assetCode,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ) {
        $this->assetIdentifier = $assetIdentifier;
        $this->assetCode = $assetCode;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }

    public function getAssetIdentifier(): AssetIdentifier
    {
        return $this->assetIdentifier;
    }

    public function getAssetCode(): AssetCode
    {
        return $this->assetCode;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Event;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered when all assets belonging to an asset family are deleted from DB
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @internal
 */
class AssetFamilyAssetsDeletedEvent extends Event
{
    /** @var AssetFamilyIdentifier */
    private $assetFamilyIdentifier;

    public function __construct(AssetFamilyIdentifier $assetFamilyIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface IndexByAssetFamilyInBackgroundInterface
{
    public function execute(AssetFamilyIdentifier $assetFamilyIdentifier): void;
}

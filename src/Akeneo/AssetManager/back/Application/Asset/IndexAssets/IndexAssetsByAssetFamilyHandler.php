<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\IndexAssets;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;

/**
 * Indexes all the assets of a given asset family
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetsByAssetFamilyHandler
{
    private AssetIndexerInterface $assetIndexer;

    public function __construct(AssetIndexerInterface $assetIndexer)
    {
        $this->assetIndexer = $assetIndexer;
    }

    public function __invoke(IndexAssetsByAssetFamilyCommand $command) :void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier);
        $this->assetIndexer->indexByAssetFamily($assetFamilyIdentifier);
    }
}
